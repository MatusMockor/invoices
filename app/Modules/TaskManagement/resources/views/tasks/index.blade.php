@php
    $apiRoutes = [
        'index' => route('api.taskmanagement.tasks.index'),
        'calendar' => route('api.taskmanagement.tasks.calendar'),
        'store' => route('api.taskmanagement.tasks.store'),
        'show' => route('api.taskmanagement.tasks.show', ['id' => ':id']),
        'update' => route('api.taskmanagement.tasks.update', ['id' => ':id']),
        'destroy' => route('api.taskmanagement.tasks.destroy', ['id' => ':id']),
        'complete' => route('api.taskmanagement.tasks.complete', ['id' => ':id']),
        'inProgress' => route('api.taskmanagement.tasks.in-progress', ['id' => ':id']),
        'cancel' => route('api.taskmanagement.tasks.cancel', ['id' => ':id']),
        'assign' => route('api.taskmanagement.tasks.assign', ['id' => ':id']),
        'addFollowUp' => route('api.taskmanagement.tasks.follow-ups.store', ['id' => ':id']),
    ];
@endphp

<x-app-layout>
    <task-management-module
        :api-routes='@json($apiRoutes)'
        csrf-token="{{ csrf_token() }}"
    ></task-management-module>
</x-app-layout>
