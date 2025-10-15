<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Database\Seeders;

use App\Models\User;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Enums\BreakType;
use App\Modules\Attendance\Enums\WorkType;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceBreak;
use App\Modules\Attendance\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::with('currentCompany')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please create users first.');

            return;
        }

        $this->command->info('Seeding attendance data...');

        foreach ($users as $user) {
            if (! $user->currentCompany) {
                continue;
            }

            $companyId = $user->current_company_id;

            // Create work schedule for Monday-Friday
            for ($day = 1; $day <= 5; $day++) {
                WorkSchedule::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00:00',
                    'end_time' => '16:00:00',
                    'is_active' => true,
                ]);
            }

            // Generate attendance for last 30 days
            for ($i = 30; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);

                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }

                // 90% chance of attendance
                if (rand(1, 100) <= 90) {
                    $checkIn = $date->copy()->setTime(8, rand(0, 30), 0);
                    $checkOut = $date->copy()->setTime(16, rand(0, 59), 0);

                    $attendance = Attendance::create([
                        'company_id' => $companyId,
                        'user_id' => $user->id,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'work_type' => fake()->randomElement([
                            WorkType::OFFICE,
                            WorkType::OFFICE,
                            WorkType::OFFICE, // Higher chance for office
                            WorkType::HOME_OFFICE,
                            WorkType::BUSINESS_TRIP,
                        ]),
                        'status' => $i < 7 ? AttendanceStatus::PENDING : AttendanceStatus::APPROVED,
                        'note' => rand(1, 100) <= 20 ? fake()->sentence() : null,
                        'check_in_ip' => fake()->ipv4(),
                        'check_out_ip' => fake()->ipv4(),
                        'total_minutes' => $checkIn->diffInMinutes($checkOut),
                    ]);

                    // Add lunch break (80% chance)
                    if (rand(1, 100) <= 80) {
                        $lunchStart = $date->copy()->setTime(12, rand(0, 30), 0);
                        $lunchEnd = $lunchStart->copy()->addMinutes(rand(30, 60));

                        AttendanceBreak::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $lunchStart,
                            'break_end' => $lunchEnd,
                            'break_type' => BreakType::LUNCH,
                            'duration_minutes' => $lunchStart->diffInMinutes($lunchEnd),
                        ]);

                        // Recalculate total minutes excluding break
                        $attendance->update([
                            'total_minutes' => $attendance->calculateTotalMinutes(),
                        ]);
                    }

                    // Add coffee break (30% chance)
                    if (rand(1, 100) <= 30) {
                        $coffeeStart = $date->copy()->setTime(10, rand(0, 30), 0);
                        $coffeeEnd = $coffeeStart->copy()->addMinutes(rand(10, 20));

                        AttendanceBreak::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $coffeeStart,
                            'break_end' => $coffeeEnd,
                            'break_type' => BreakType::COFFEE,
                            'duration_minutes' => $coffeeStart->diffInMinutes($coffeeEnd),
                        ]);

                        // Recalculate total minutes
                        $attendance->update([
                            'total_minutes' => $attendance->calculateTotalMinutes(),
                        ]);
                    }
                }
            }

            // Create one active attendance for today (50% chance)
            if (rand(1, 100) <= 50 && Carbon::today()->isWeekday()) {
                $todayCheckIn = Carbon::today()->setTime(8, rand(0, 30), 0);

                Attendance::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'check_in' => $todayCheckIn,
                    'check_out' => null,
                    'work_type' => fake()->randomElement([
                        WorkType::OFFICE,
                        WorkType::HOME_OFFICE,
                    ]),
                    'status' => AttendanceStatus::PENDING,
                    'check_in_ip' => fake()->ipv4(),
                    'total_minutes' => null,
                ]);
            }

            $this->command->info("Created attendance data for user: {$user->name}");
        }

        $this->command->info('Attendance seeding completed!');
    }
}
