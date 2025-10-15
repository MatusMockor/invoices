<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Enums\WorkType;
use App\Modules\Attendance\Filters\DateRangeFilter;
use App\Modules\Attendance\Filters\StatusFilter;
use App\Modules\Attendance\Filters\UserFilter;
use App\Modules\Attendance\Filters\WorkTypeFilter;
use App\Modules\Attendance\Http\Requests\ApproveAttendanceRequest;
use App\Modules\Attendance\Http\Requests\CheckInRequest;
use App\Modules\Attendance\Http\Requests\CheckOutRequest;
use App\Modules\Attendance\Http\Requests\StartBreakRequest;
use App\Modules\Attendance\Http\Requests\UpdateAttendanceRequest;
use App\Modules\Attendance\Http\Resources\AttendanceBreakResource;
use App\Modules\Attendance\Http\Resources\AttendanceResource;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceBreak;
use App\Modules\Attendance\Services\Interfaces\AttendanceServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pipeline\Pipeline;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceServiceInterface $attendanceService
    ) {}

    /**
     * Display a listing of attendances.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Attendance::class);

        $companyId = auth()->user()->current_company_id;
        $perPage = $request->input('per_page', 15);

        $query = Attendance::with(['user', 'breaks', 'approvedBy'])
            ->byCompany($companyId);

        // Apply filters using Pipeline
        $attendances = app(Pipeline::class)
            ->send($query)
            ->through([
                DateRangeFilter::class,
                UserFilter::class,
                WorkTypeFilter::class,
                StatusFilter::class,
            ])
            ->thenReturn()
            ->orderBy('check_in', 'desc')
            ->paginate($perPage);

        return AttendanceResource::collection($attendances);
    }

    /**
     * Get today's attendance for the authenticated user.
     */
    public function today(): JsonResponse
    {
        $userId = auth()->id();
        $companyId = auth()->user()->current_company_id;

        $attendance = $this->attendanceService->getTodayAttendance($userId, $companyId);

        return response()->json([
            'attendance' => $attendance ? new AttendanceResource($attendance) : null,
        ]);
    }

    /**
     * Check in (start attendance).
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);

        try {
            $attendance = $this->attendanceService->checkIn(
                auth()->id(),
                auth()->user()->current_company_id,
                $request->validated()
            );

            return response()->json([
                'message' => 'Úspešne ste sa prihlásili.',
                'attendance' => new AttendanceResource($attendance),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check out (end attendance).
     */
    public function checkOut(CheckOutRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('update', $attendance);

        try {
            $attendance = $this->attendanceService->checkOut($attendance, $request->validated());

            return response()->json([
                'message' => 'Úspešne ste sa odhlásili.',
                'attendance' => new AttendanceResource($attendance),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Start a break.
     */
    public function startBreak(StartBreakRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('update', $attendance);

        try {
            $break = $this->attendanceService->startBreak($attendance, $request->validated());

            return response()->json([
                'message' => 'Prestávka bola začatá.',
                'break' => new AttendanceBreakResource($break),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * End a break.
     */
    public function endBreak(AttendanceBreak $break): JsonResponse
    {
        $this->authorize('update', $break->attendance);

        try {
            $break = $this->attendanceService->endBreak($break);

            return response()->json([
                'message' => 'Prestávka bola ukončená.',
                'break' => new AttendanceBreakResource($break),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance): AttendanceResource
    {
        $this->authorize('view', $attendance);

        $attendance->load(['user', 'breaks', 'approvedBy']);

        return new AttendanceResource($attendance);
    }

    /**
     * Update the specified attendance.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('update', $attendance);

        try {
            $attendance = $this->attendanceService->updateAttendance($attendance, $request->validated());

            return response()->json([
                'message' => 'Dochádzka bola aktualizovaná.',
                'attendance' => new AttendanceResource($attendance),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified attendance.
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $this->authorize('delete', $attendance);

        $attendance->delete();

        return response()->json([
            'message' => 'Dochádzka bola vymazaná.',
        ]);
    }

    /**
     * Approve the specified attendance.
     */
    public function approve(ApproveAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('approve', $attendance);

        try {
            $attendance = $this->attendanceService->approveAttendance(
                $attendance,
                auth()->id(),
                $request->input('note')
            );

            return response()->json([
                'message' => 'Dochádzka bola schválená.',
                'attendance' => new AttendanceResource($attendance),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject the specified attendance.
     */
    public function reject(ApproveAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('approve', $attendance);

        try {
            $attendance = $this->attendanceService->rejectAttendance(
                $attendance,
                auth()->id(),
                $request->input('note', 'Zamietnuté')
            );

            return response()->json([
                'message' => 'Dochádzka bola zamietnutá.',
                'attendance' => new AttendanceResource($attendance),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get monthly report.
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);

        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $companyId = auth()->user()->current_company_id;
        $year = $request->input('year');
        $month = $request->input('month');
        $userId = $request->input('user_id');

        // If no user_id provided, use authenticated user
        if (! $userId && ! auth()->user()->hasPermissionTo('attendance.view-all')) {
            $userId = auth()->id();
        }

        $report = $this->attendanceService->getMonthlyReport($companyId, $year, $month, $userId);

        return response()->json([
            'attendances' => AttendanceResource::collection($report['attendances']),
            'summary' => $report['summary'],
        ]);
    }

    /**
     * Get enum options for dropdowns.
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'work_types' => WorkType::options(),
            'statuses' => AttendanceStatus::options(),
        ]);
    }
}
