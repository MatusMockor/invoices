@php
    $apiRoutes = [
        'index' => route('api.attendance.attendance.attendances.index'),
        'today' => route('api.attendance.attendance.today'),
        'checkIn' => route('api.attendance.attendance.check-in'),
        'checkOut' => route('api.attendance.attendance.check-out', ['attendance' => ':id']),
        'show' => route('api.attendance.attendance.attendances.show', ['attendance' => ':id']),
        'update' => route('api.attendance.attendance.attendances.update', ['attendance' => ':id']),
        'destroy' => route('api.attendance.attendance.attendances.destroy', ['attendance' => ':id']),
        'startBreak' => route('api.attendance.attendance.breaks.start', ['attendance' => ':id']),
        'endBreak' => route('api.attendance.attendance.breaks.end', ['break' => ':breakId']),
        'approve' => route('api.attendance.attendance.approve', ['attendance' => ':id']),
        'reject' => route('api.attendance.attendance.reject', ['attendance' => ':id']),
        'monthlyReport' => route('api.attendance.attendance.monthly-report'),
        'options' => route('api.attendance.attendance.options'),
    ];
@endphp

<x-app-layout>
    <attendance-module
        :api-routes='@json($apiRoutes)'
        csrf-token="{{ csrf_token() }}"
    ></attendance-module>
</x-app-layout>
