@php
    $apiRoutes = [
        'index' => route('api.attendance.index'),
        'today' => route('api.attendance.today'),
        'checkIn' => route('api.attendance.check-in'),
        'checkOut' => route('api.attendance.check-out', ['attendance' => ':id']),
        'show' => route('api.attendance.show', ['attendance' => ':id']),
        'update' => route('api.attendance.update', ['attendance' => ':id']),
        'destroy' => route('api.attendance.destroy', ['attendance' => ':id']),
        'startBreak' => route('api.attendance.start-break', ['attendance' => ':id']),
        'endBreak' => route('api.attendance.end-break', ['break' => ':breakId']),
        'approve' => route('api.attendance.approve', ['attendance' => ':id']),
        'reject' => route('api.attendance.reject', ['attendance' => ':id']),
        'monthlyReport' => route('api.attendance.monthly-report'),
        'options' => route('api.attendance.options'),
    ];
@endphp

<x-app-layout>
    <attendance-module
        :api-routes='@json($apiRoutes)'
        csrf-token="{{ csrf_token() }}"
    ></attendance-module>
</x-app-layout>
