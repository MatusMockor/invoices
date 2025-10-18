<?php

declare(strict_types=1);

namespace App\Modules\Attendance\tests\Unit\Repositories;

use App\Models\User;
use App\Models\UserCompany;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Enums\WorkType;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceBreak;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceRepository $repository;

    protected User $user;

    protected UserCompany $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new AttendanceRepository;
        $this->user = User::factory()->create();
        $this->company = UserCompany::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_create_stores_new_attendance_record(): void
    {
        $data = [
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
            'work_type' => WorkType::OFFICE,
            'status' => AttendanceStatus::PENDING,
        ];

        $attendance = $this->repository->create($data);

        $this->assertInstanceOf(Attendance::class, $attendance);
        $this->assertEquals($this->user->id, $attendance->user_id);
        $this->assertEquals($this->company->id, $attendance->company_id);
        $this->assertEquals(WorkType::OFFICE, $attendance->work_type);
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_update_modifies_existing_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'work_type' => WorkType::OFFICE,
            'note' => 'Original note',
        ]);

        $updatedData = [
            'work_type' => WorkType::HOME_OFFICE,
            'note' => 'Updated note',
        ];

        $result = $this->repository->update($attendance, $updatedData);

        $this->assertEquals(WorkType::HOME_OFFICE, $result->work_type);
        $this->assertEquals('Updated note', $result->note);
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'work_type' => WorkType::HOME_OFFICE->value,
            'note' => 'Updated note',
        ]);
    }

    public function test_update_returns_fresh_model(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'note' => 'Original',
        ]);

        $result = $this->repository->update($attendance, ['note' => 'Updated']);

        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals('Updated', $result->note);
        $this->assertTrue($result->wasRecentlyCreated === false);
    }

    public function test_delete_removes_attendance_record(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
        ]);

        $result = $this->repository->delete($attendance);

        $this->assertTrue($result);
        $this->assertSoftDeleted('attendances', ['id' => $attendance->id]);
    }

    public function test_find_by_id_returns_attendance_with_relationships(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
        ]);

        AttendanceBreak::factory()->count(2)->create([
            'attendance_id' => $attendance->id,
        ]);

        $result = $this->repository->findById($attendance->id);

        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals($attendance->id, $result->id);
        $this->assertTrue($result->relationLoaded('user'));
        $this->assertTrue($result->relationLoaded('breaks'));
        $this->assertCount(2, $result->breaks);
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById(999999);

        $this->assertNull($result);
    }

    public function test_get_active_attendance_for_user_returns_unchecked_out_attendance(): void
    {
        $activeAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(2),
            'check_out' => null,
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subDays(1),
            'check_out' => Carbon::now()->subDays(1)->addHours(8),
        ]);

        $result = $this->repository->getActiveAttendanceForUser($this->user->id, $this->company->id);

        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals($activeAttendance->id, $result->id);
        $this->assertNull($result->check_out);
    }

    public function test_get_active_attendance_for_user_returns_null_when_all_checked_out(): void
    {
        Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subDays(1),
            'check_out' => Carbon::now()->subDays(1)->addHours(8),
        ]);

        $result = $this->repository->getActiveAttendanceForUser($this->user->id, $this->company->id);

        $this->assertNull($result);
    }

    public function test_get_active_attendance_for_user_returns_latest_when_multiple_active(): void
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(5),
            'check_out' => null,
        ]);

        $latestAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(2),
            'check_out' => null,
        ]);

        $result = $this->repository->getActiveAttendanceForUser($this->user->id, $this->company->id);

        $this->assertEquals($latestAttendance->id, $result->id);
    }

    public function test_get_attendances_by_date_range_returns_filtered_attendances(): void
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $inRangeAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subMonths(2),
        ]);

        $result = $this->repository->getAttendancesByDateRange(
            $this->company->id,
            $startDate->toDateTimeString(),
            $endDate->toDateTimeString(),
            $this->user->id
        );

        $this->assertCount(1, $result);
        $this->assertEquals($inRangeAttendance->id, $result->first()->id);
    }

    public function test_get_attendances_by_date_range_filters_by_user(): void
    {
        $otherUser = User::factory()->create(['current_company_id' => $this->company->id]);
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $userAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
        ]);

        $result = $this->repository->getAttendancesByDateRange(
            $this->company->id,
            $startDate->toDateTimeString(),
            $endDate->toDateTimeString(),
            $this->user->id
        );

        $this->assertCount(1, $result);
        $this->assertEquals($userAttendance->id, $result->first()->id);
    }

    public function test_get_attendances_by_date_range_returns_all_users_when_user_id_null(): void
    {
        $otherUser = User::factory()->create(['current_company_id' => $this->company->id]);
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
        ]);

        $result = $this->repository->getAttendancesByDateRange(
            $this->company->id,
            $startDate->toDateTimeString(),
            $endDate->toDateTimeString(),
            null
        );

        $this->assertCount(2, $result);
    }

    public function test_get_attendances_by_date_range_loads_relationships(): void
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now(),
        ]);

        AttendanceBreak::factory()->create(['attendance_id' => $attendance->id]);

        $result = $this->repository->getAttendancesByDateRange(
            $this->company->id,
            Carbon::now()->startOfDay()->toDateTimeString(),
            Carbon::now()->endOfDay()->toDateTimeString()
        );

        $firstAttendance = $result->first();
        $this->assertTrue($firstAttendance->relationLoaded('user'));
        $this->assertTrue($firstAttendance->relationLoaded('breaks'));
        $this->assertTrue($firstAttendance->relationLoaded('approvedBy'));
    }

    public function test_get_monthly_attendances_returns_attendances_for_specific_month(): void
    {
        $year = 2025;
        $month = 3;

        $marchAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month, 15, 8, 0),
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month + 1, 15, 8, 0),
        ]);

        $result = $this->repository->getMonthlyAttendances($this->company->id, $year, $month, $this->user->id);

        $this->assertCount(1, $result);
        $this->assertEquals($marchAttendance->id, $result->first()->id);
    }

    public function test_get_monthly_attendances_handles_month_boundaries_correctly(): void
    {
        $year = 2025;
        $month = 2;

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month, 1, 0, 0),
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month, 28, 23, 59),
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::create($year, $month + 1, 1, 0, 0),
        ]);

        $result = $this->repository->getMonthlyAttendances($this->company->id, $year, $month, $this->user->id);

        $this->assertCount(2, $result);
    }

    public function test_get_pending_approvals_returns_only_pending_checked_out_attendances(): void
    {
        $pendingCheckedOut = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
            'status' => AttendanceStatus::PENDING,
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
            'status' => AttendanceStatus::PENDING,
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subDays(1),
            'check_out' => Carbon::now()->subDays(1)->addHours(8),
            'status' => AttendanceStatus::APPROVED,
        ]);

        $result = $this->repository->getPendingApprovals($this->company->id);

        $this->assertCount(1, $result);
        $this->assertEquals($pendingCheckedOut->id, $result->first()->id);
    }

    public function test_get_pending_approvals_orders_by_check_out_desc(): void
    {
        $olderPending = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subDays(2),
            'check_out' => Carbon::now()->subDays(2)->addHours(8),
            'status' => AttendanceStatus::PENDING,
        ]);

        $newerPending = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subDays(1),
            'check_out' => Carbon::now()->subDays(1)->addHours(8),
            'status' => AttendanceStatus::PENDING,
        ]);

        $result = $this->repository->getPendingApprovals($this->company->id);

        $this->assertEquals($newerPending->id, $result->first()->id);
        $this->assertEquals($olderPending->id, $result->last()->id);
    }

    public function test_get_pending_approvals_filters_by_company(): void
    {
        $otherCompany = UserCompany::factory()->create();

        $companyAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
            'status' => AttendanceStatus::PENDING,
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $otherCompany->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
            'status' => AttendanceStatus::PENDING,
        ]);

        $result = $this->repository->getPendingApprovals($this->company->id);

        $this->assertCount(1, $result);
        $this->assertEquals($companyAttendance->id, $result->first()->id);
    }
}
