<?php

declare(strict_types=1);

namespace App\Modules\Attendance\tests\Unit\Services;

use App\Models\Company;
use App\Models\User;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Enums\BreakType;
use App\Modules\Attendance\Enums\WorkType;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceBreak;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\Attendance\Services\AttendanceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $service;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create(['user_id' => $this->user->id]);

        $repository = new AttendanceRepository;
        $this->service = new AttendanceService($repository);
    }

    public function test_check_in_creates_new_attendance_record(): void
    {
        $data = [
            'work_type' => WorkType::OFFICE,
            'note' => 'Starting work',
        ];

        $attendance = $this->service->checkIn($this->user->id, $this->company->id, $data);

        $this->assertInstanceOf(Attendance::class, $attendance);
        $this->assertEquals($this->user->id, $attendance->user_id);
        $this->assertEquals($this->company->id, $attendance->company_id);
        $this->assertEquals(WorkType::OFFICE, $attendance->work_type);
        $this->assertEquals('Starting work', $attendance->note);
        $this->assertEquals(AttendanceStatus::PENDING, $attendance->status);
        $this->assertNotNull($attendance->check_in);
        $this->assertNotNull($attendance->check_in_ip);
    }

    public function test_check_in_throws_exception_when_user_already_has_active_attendance(): void
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
            'check_out' => null,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Používateľ už má aktívnu dochádzku. Najprv sa musí odhlásiť.');

        $this->service->checkIn($this->user->id, $this->company->id, [
            'work_type' => WorkType::OFFICE,
        ]);
    }

    public function test_check_out_updates_attendance_with_check_out_time(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => null,
        ]);

        $result = $this->service->checkOut($attendance, ['note' => 'End of work']);

        $this->assertNotNull($result->check_out);
        $this->assertNotNull($result->check_out_ip);
        $this->assertNotNull($result->total_minutes);
        $this->assertEquals('End of work', $result->note);
        $this->assertGreaterThan(0, $result->total_minutes);
    }

    public function test_check_out_throws_exception_when_already_checked_out(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dochádzka už bola ukončená.');

        $this->service->checkOut($attendance);
    }

    public function test_check_out_ends_active_breaks_automatically(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => null,
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(15),
            'break_end' => null,
        ]);

        $result = $this->service->checkOut($attendance);

        $break->refresh();
        $this->assertNotNull($break->break_end);
        $this->assertNotNull($break->duration_minutes);
    }

    public function test_check_out_calculates_total_minutes_correctly(): void
    {
        $checkIn = Carbon::now()->subHours(8);
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => $checkIn,
            'check_out' => null,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $checkIn->copy()->addHours(4),
            'break_end' => $checkIn->copy()->addHours(4)->addMinutes(30),
            'duration_minutes' => 30,
        ]);

        $result = $this->service->checkOut($attendance);

        $expectedMinutes = 480 - 30; // 8 hours - 30 minutes break
        $this->assertEquals($expectedMinutes, $result->total_minutes);
    }

    public function test_start_break_creates_new_break_record(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
        ]);

        $data = [
            'break_type' => BreakType::LUNCH,
            'note' => 'Lunch break',
        ];

        $break = $this->service->startBreak($attendance, $data);

        $this->assertInstanceOf(AttendanceBreak::class, $break);
        $this->assertEquals($attendance->id, $break->attendance_id);
        $this->assertEquals(BreakType::LUNCH, $break->break_type);
        $this->assertEquals('Lunch break', $break->note);
        $this->assertNotNull($break->break_start);
        $this->assertNull($break->break_end);
    }

    public function test_start_break_throws_exception_when_attendance_checked_out(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nemožno začať prestávku, dochádzka už bola ukončená.');

        $this->service->startBreak($attendance, ['break_type' => BreakType::LUNCH]);
    }

    public function test_start_break_throws_exception_when_break_already_active(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(10),
            'break_end' => null,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Už prebieha aktívna prestávka.');

        $this->service->startBreak($attendance, ['break_type' => BreakType::COFFEE]);
    }

    public function test_end_break_updates_break_with_end_time(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(30),
            'break_end' => null,
        ]);

        $result = $this->service->endBreak($break);

        $this->assertNotNull($result->break_end);
        $this->assertNotNull($result->duration_minutes);
        $this->assertGreaterThan(0, $result->duration_minutes);
    }

    public function test_end_break_throws_exception_when_already_ended(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(30),
            'break_end' => Carbon::now(),
            'duration_minutes' => 30,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Prestávka už bola ukončená.');

        $this->service->endBreak($break);
    }

    public function test_update_attendance_modifies_attendance_data(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'work_type' => WorkType::OFFICE,
            'note' => 'Original note',
        ]);

        $data = [
            'work_type' => WorkType::HOME_OFFICE,
            'note' => 'Updated note',
        ];

        $result = $this->service->updateAttendance($attendance, $data);

        $this->assertEquals(WorkType::HOME_OFFICE, $result->work_type);
        $this->assertEquals('Updated note', $result->note);
    }

    public function test_approve_attendance_updates_status_to_approved(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
            'status' => AttendanceStatus::PENDING,
        ]);

        $approver = User::factory()->create();
        $result = $this->service->approveAttendance($attendance, $approver->id, 'Approved by manager');

        $this->assertEquals(AttendanceStatus::APPROVED, $result->status);
        $this->assertEquals($approver->id, $result->approved_by);
        $this->assertNotNull($result->approved_at);
        $this->assertEquals('Approved by manager', $result->approval_note);
    }

    public function test_approve_attendance_throws_exception_when_already_approved(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'status' => AttendanceStatus::APPROVED,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dochádzka už bola schválená.');

        $this->service->approveAttendance($attendance, $this->user->id);
    }

    public function test_approve_attendance_throws_exception_when_not_checked_out(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
            'status' => AttendanceStatus::PENDING,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nemožno schváliť dochádzku, ktorá ešte nebola ukončená.');

        $this->service->approveAttendance($attendance, $this->user->id);
    }

    public function test_reject_attendance_updates_status_to_rejected(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
            'status' => AttendanceStatus::PENDING,
        ]);

        $rejector = User::factory()->create();
        $result = $this->service->rejectAttendance($attendance, $rejector->id, 'Invalid times');

        $this->assertEquals(AttendanceStatus::REJECTED, $result->status);
        $this->assertEquals($rejector->id, $result->approved_by);
        $this->assertNotNull($result->approved_at);
        $this->assertEquals('Invalid times', $result->approval_note);
    }

    public function test_reject_attendance_throws_exception_when_already_approved(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'status' => AttendanceStatus::APPROVED,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nemožno zamietnuť už schválenú dochádzku.');

        $this->service->rejectAttendance($attendance, $this->user->id, 'Rejected');
    }

    public function test_get_monthly_report_returns_correct_summary(): void
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        Attendance::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month, 1, 8, 0),
            'check_out' => Carbon::create($year, $month, 1, 16, 0),
            'total_minutes' => 480,
            'status' => AttendanceStatus::APPROVED,
        ]);

        Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month, 2, 8, 0),
            'check_out' => Carbon::create($year, $month, 2, 16, 0),
            'total_minutes' => 480,
            'status' => AttendanceStatus::PENDING,
        ]);

        $report = $this->service->getMonthlyReport($this->company->id, $year, $month, $this->user->id);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('attendances', $report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertEquals(8, $report['summary']['total_days']);
        $this->assertEquals(5, $report['summary']['approved_days']);
        $this->assertEquals(3, $report['summary']['pending_days']);
        $this->assertEquals(64.0, $report['summary']['total_hours']); // 8 days * 8 hours
    }

    public function test_get_monthly_report_filters_by_user(): void
    {
        $otherUser = User::factory()->create(['current_company_id' => $this->company->id]);
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month, 1, 8, 0),
        ]);

        Attendance::factory()->count(2)->create([
            'user_id' => $otherUser->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month, 1, 8, 0),
        ]);

        $report = $this->service->getMonthlyReport($this->company->id, $year, $month, $this->user->id);

        $this->assertEquals(3, $report['summary']['total_days']);
    }

    public function test_get_today_attendance_returns_current_day_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
            'check_out' => null,
        ]);

        $result = $this->service->getTodayAttendance($this->user->id, $this->company->id);

        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals($attendance->id, $result->id);
    }

    public function test_get_today_attendance_returns_null_when_no_attendance(): void
    {
        $result = $this->service->getTodayAttendance($this->user->id, $this->company->id);

        $this->assertNull($result);
    }

    public function test_get_today_attendance_does_not_return_old_attendance(): void
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subDays(2),
            'check_out' => Carbon::now()->subDays(2)->addHours(8),
        ]);

        $result = $this->service->getTodayAttendance($this->user->id, $this->company->id);

        $this->assertNull($result);
    }
}
