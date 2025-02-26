{{-- <!-- resources/views/components/task-item.blade.php -->
@props(['todo', 'showDate' => false])

@php
    $categoryColor = $todo->category ? $todo->category->color : '#3490dc';
@endphp

<div class="task-item group">
    <div class="border-l-4 p-2 rounded shadow-sm text-sm cursor-pointer hover:bg-gray-100 transition-colors"
         style="border-color: {{ $categoryColor }}; background-color: {{ $categoryColor }}15;"
         onclick="editTodo({{ $todo->id }}, '{{ addslashes($todo->title) }}', '{{ $todo->due_date?->format('Y-m-d') }}', '{{ $todo->due_time?->format('H:i') }}', {{ $todo->category_id ?? 'null' }}, '{{ $todo->recurrence_type ?? 'none' }}')">
        <div class="flex items-center">
            <form action="{{ route('todos.toggle', $todo) }}" method="POST" class="mr-1 flex-shrink-0" onclick="event.stopPropagation()">
                @csrf
                @method('PATCH')
                <input type="checkbox" onChange="this.form.submit()"
                       {{ $todo->status === 'completed' ? 'checked' : '' }}
                       class="w-3 h-3">
            </form>

            <span class="truncate {{ $todo->status === 'completed' ? 'line-through opacity-60' : '' }}">
                {{ $todo->title }}
                @if($todo->recurrence_type)
                    <span class="text-xs text-gray-500 ml-1">
                        @switch($todo->recurrence_type)
                            @case('daily')
                                毎日
                                @break
                            @case('weekly')
                                毎週
                                @break
                            @case('monthly')
                                毎月
                                @break
                        @endswitch
                    </span>
                @endif
            </span>

            @if($showDate && $todo->due_date)
                <span class="ml-auto text-xs text-gray-500 mr-1 flex-shrink-0">
                    {{ $todo->due_date->format('m/d') }}
                </span>
            @endif

            @if($todo->due_time)
                <span class="ml-auto text-xs text-gray-500 flex-shrink-0">
                    {{ $todo->due_time->format('H:i') }}
                </span>
            @endif
        </div>
    </div>
</div> --}}
