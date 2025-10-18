<?php

declare(strict_types=1);

namespace App\Modules\Attendance\tests\Feature\Http\Controllers\Api;

use App\Models\Company;
use App\Models\User;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Enums\BreakType;
use App\Modules\Attendance\Enums\WorkType;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create(['user_id' => $this->user->id]);
        $this->user->update(['current_company_id' => $this->company->id]);

        $this->actingAs($this->user);
    }

    public function test_index_returns_paginated_attendances(): void
    {
        Attendance::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.attendance.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user',
                        'check_in',
                        'check_out',
                        'work_type',
                        'status',
                        'total_minutes',
                    ],
                ],
                'meta',
                'links',
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_index_filters_attendances_by_date_range(): void
    {
        Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subDays(10),
        ]);

        $recentAttendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subDays(2),
        ]);

        $response = $this->getJson(route('api.attendance.index', [
            'start_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($recentAttendance->id, $response->json('data.0.id'));
    }

    public function test_index_filters_attendances_by_user(): void
    {
        $otherUser = User::factory()->create(['current_company_id' => $this->company->id]);

        Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $otherUserAttendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson(route('api.attendance.index', [
            'user_id' => $otherUser->id,
        ]));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($otherUserAttendance->id, $response->json('data.0.id'));
    }

    public function test_today_returns_current_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now(),
            'check_out' => null,
        ]);

        $response = $this->getJson(route('api.attendance.today'));

        $response->assertStatus(200)
            ->assertJsonStructure(['attendance'])
            ->assertJson([
                'attendance' => [
                    'id' => $attendance->id,
                ],
            ]);
    }

    public function test_today_returns_null_when_no_attendance(): void
    {
        $response = $this->getJson(route('api.attendance.today'));

        $response->assertStatus(200)
            ->assertJson(['attendance' => null]);
    }

    public function test_check_in_creates_new_attendance(): void
    {
        $data = [
            'work_type' => WorkType::OFFICE->value,
            'note' => 'Starting work',
            'check_in_latitude' => 48.1486,
            'check_in_longitude' => 17.1077,
        ];

        $response = $this->postJson(route('api.attendance.check-in'), $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'attendance' => ['id', 'check_in', 'work_type', 'status'],
            ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'work_type' => WorkType::OFFICE,
            'note' => 'Starting work',
            'status' => AttendanceStatus::PENDING,
        ]);
    }

    public function test_check_in_fails_when_already_checked_in(): void
    {
        Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now(),
            'check_out' => null,
        ]);

        $data = [
            'work_type' => WorkType::OFFICE->value,
        ];

        $response = $this->postJson(route('api.attendance.check-in'), $data);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Používateľ už má aktívnu dochádzku. Najprv sa musí odhlásiť.',
            ]);
    }

    public function test_check_in_validates_required_fields(): void
    {
        $response = $this->postJson(route('api.attendance.check-in'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['work_type']);
    }

    public function test_check_in_validates_work_type_enum(): void
    {
        $response = $this->postJson(route('api.attendance.check-in'), [
            'work_type' => 'invalid_type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['work_type']);
    }

    public function test_check_out_updates_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => null,
        ]);

        $data = [
            'note' => 'Ending work',
            'check_out_latitude' => 48.1486,
            'check_out_longitude' => 17.1077,
        ];

        $response = $this->postJson(route('api.attendance.check-out', $attendance), $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'attendance' => ['id', 'check_out', 'total_minutes'],
            ]);

        $attendance->refresh();
        $this->assertNotNull($attendance->check_out);
        $this->assertNotNull($attendance->total_minutes);
        $this->assertEquals('Ending work', $attendance->note);
    }

    public function test_check_out_fails_when_already_checked_out(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
        ]);

        $response = $this->postJson(route('api.attendance.check-out', $attendance), []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Dochádzka už bola ukončená.',
            ]);
    }

    public function test_check_out_ends_active_breaks(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => null,
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(15),
            'break_end' => null,
        ]);

        $response = $this->postJson(route('api.attendance.check-out', $attendance), []);

        $response->assertStatus(200);

        $break->refresh();
        $this->assertNotNull($break->break_end);
        $this->assertNotNull($break->duration_minutes);
    }

    public function test_start_break_creates_new_break(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
        ]);

        $data = [
            'break_type' => BreakType::LUNCH->value,
            'note' => 'Lunch break',
        ];

        $response = $this->postJson(route('api.attendance.start-break', $attendance), $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'break' => ['id', 'break_start', 'break_type'],
            ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_type' => BreakType::LUNCH,
            'note' => 'Lunch break',
        ]);
    }

    public function test_start_break_fails_when_attendance_checked_out(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
        ]);

        $data = [
            'break_type' => BreakType::LUNCH->value,
        ];

        $response = $this->postJson(route('api.attendance.start-break', $attendance), $data);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Nemožno začať prestávku, dochádzka už bola ukončená.',
            ]);
    }

    public function test_start_break_fails_when_break_already_active(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(10),
            'break_end' => null,
        ]);

        $data = [
            'break_type' => BreakType::LUNCH->value,
        ];

        $response = $this->postJson(route('api.attendance.start-break', $attendance), $data);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Už prebieha aktívna prestávka.',
            ]);
    }

    public function test_end_break_updates_break(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(30),
            'break_end' => null,
        ]);

        $response = $this->postJson(route('api.attendance.end-break', $break));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'break' => ['id', 'break_end', 'duration_minutes'],
            ]);

        $break->refresh();
        $this->assertNotNull($break->break_end);
        $this->assertNotNull($break->duration_minutes);
        $this->assertGreaterThan(0, $break->duration_minutes);
    }

    public function test_end_break_fails_when_already_ended(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(30),
            'break_end' => Carbon::now(),
            'duration_minutes' => 30,
        ]);

        $response = $this->postJson(route('api.attendance.end-break', $break));

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Prestávka už bola ukončená.',
            ]);
    }

    public function test_show_returns_attendance_details(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        AttendanceBreak::factory()->count(2)->create([
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->getJson(route('api.attendance.show', $attendance));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user',
                    'check_in',
                    'check_out',
                    'work_type',
                    'status',
                    'breaks',
                    'total_minutes',
                ],
            ]);
    }

    public function test_update_modifies_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'work_type' => WorkType::OFFICE,
            'note' => 'Original note',
        ]);

        $data = [
            'work_type' => WorkType::HOME_OFFICE->value,
            'note' => 'Updated note',
            'check_in' => Carbon::now()->subHours(8)->toDateTimeString(),
            'check_out' => Carbon::now()->toDateTimeString(),
        ];

        $response = $this->putJson(route('api.attendance.update', $attendance), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'attendance']);

        $attendance->refresh();
        $this->assertEquals(WorkType::HOME_OFFICE, $attendance->work_type);
        $this->assertEquals('Updated note', $attendance->note);
    }

    public function test_destroy_deletes_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson(route('api.attendance.destroy', $attendance));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Dochádzka bola vymazaná.']);

        $this->assertSoftDeleted('attendances', ['id' => $attendance->id]);
    }

    public function test_approve_approves_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
            'status' => AttendanceStatus::PENDING,
        ]);

        $data = ['note' => 'Approved by manager'];

        $response = $this->postJson(route('api.attendance.approve', $attendance), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'attendance']);

        $attendance->refresh();
        $this->assertEquals(AttendanceStatus::APPROVED, $attendance->status);
        $this->assertEquals($this->user->id, $attendance->approved_by);
        $this->assertNotNull($attendance->approved_at);
        $this->assertEquals('Approved by manager', $attendance->approval_note);
    }

    public function test_approve_fails_when_already_approved(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => AttendanceStatus::APPROVED,
            'approved_by' => $this->user->id,
            'approved_at' => Carbon::now(),
        ]);

        $response = $this->postJson(route('api.attendance.approve', $attendance), []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Dochádzka už bola schválená.',
            ]);
    }

    public function test_approve_fails_when_not_checked_out(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
            'status' => AttendanceStatus::PENDING,
        ]);

        $response = $this->postJson(route('api.attendance.approve', $attendance), []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Nemožno schváliť dochádzku, ktorá ešte nebola ukončená.',
            ]);
    }

    public function test_reject_rejects_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
            'status' => AttendanceStatus::PENDING,
        ]);

        $data = ['note' => 'Rejected due to invalid times'];

        $response = $this->postJson(route('api.attendance.reject', $attendance), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'attendance']);

        $attendance->refresh();
        $this->assertEquals(AttendanceStatus::REJECTED, $attendance->status);
        $this->assertEquals($this->user->id, $attendance->approved_by);
        $this->assertEquals('Rejected due to invalid times', $attendance->approval_note);
    }

    public function test_reject_fails_when_already_approved(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => AttendanceStatus::APPROVED,
        ]);

        $response = $this->postJson(route('api.attendance.reject', $attendance), []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Nemožno zamietnuť už schválenú dochádzku.',
            ]);
    }

    public function test_monthly_report_returns_summary(): void
    {
        Attendance::factory()->count(10)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'check_in' => Carbon::now()->startOfMonth(),
            'check_out' => Carbon::now()->startOfMonth()->addHours(8),
            'total_minutes' => 480,
            'status' => AttendanceStatus::APPROVED,
        ]);

        $data = [
            'year' => Carbon::now()->year,
            'month' => Carbon::now()->month,
        ];

        $response = $this->getJson(route('api.attendance.monthly-report', $data));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'attendances',
                'summary' => [
                    'total_days',
                    'approved_days',
                    'pending_days',
                    'total_hours',
                    'total_break_hours',
                    'average_hours_per_day',
                ],
            ]);

        $this->assertEquals(10, $response->json('summary.total_days'));
        $this->assertEquals(10, $response->json('summary.approved_days'));
    }

    public function test_monthly_report_validates_required_fields(): void
    {
        $response = $this->getJson(route('api.attendance.monthly-report'));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year', 'month']);
    }

    public function test_options_returns_enum_values(): void
    {
        $response = $this->getJson(route('api.attendance.options'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'work_types',
                'statuses',
            ]);

        $this->assertIsArray($response->json('work_types'));
        $this->assertIsArray($response->json('statuses'));
    }

    public function test_user_cannot_access_other_company_attendances(): void
    {
        $otherCompany = Company::factory()->create();
        $otherUser = User::factory()->create(['current_company_id' => $otherCompany->id]);

        $otherAttendance = Attendance::factory()->create([
            'company_id' => $otherCompany->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson(route('api.attendance.show', $otherAttendance));

        $response->assertStatus(403);
    }
}
