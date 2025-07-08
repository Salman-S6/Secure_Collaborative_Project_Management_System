<x-mail::message>
# New Task Assigned

Hello **{{ $assignedUser->name }}**,

A new task has been assigned to you in the project **"{{ $task->project->name }}"**.

**Task:** {{ $task->name }}
**Due Date:** {{ $task->due_date?->format('Y-m-d') ?? 'N/A' }}

<x-mail::button :url="''">
View Task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
