<!-- resources/views/layouts/partials/memo-list.blade.php -->
<div class="space-y-1 max-h-96 overflow-y-auto pr-1 custom-scrollbar memo-list-container">
    @if($memos->isEmpty())
        <div class="text-xs text-gray-500 text-center py-2">メモはありません</div>
    @else
        @foreach($memos as $memo)
            <div class="group bg-gray-700 hover:bg-gray-600 rounded py-1.5 px-2 cursor-pointer transition-colors"
                 onclick="editTodo({{ $memo->id }}, {{ json_encode([
                    'title' => $memo->title,
                    'due_date' => $memo->due_date ? $memo->due_date->format('Y-m-d') : null,
                    'due_time' => $memo->due_time ? $memo->due_time->format('H:i') : null,
                    'category_id' => $memo->category_id,
                    'recurrence_type' => $memo->recurrence_type,
                    'recurrence_end_date' => $memo->recurrence_end_date ? $memo->recurrence_end_date->format('Y-m-d') : null,
                ]) }})"
                 style="border-left: 3px solid {{ $memo->category ? $memo->category->color : '#6B7280' }}">
                <div class="flex items-center justify-between">
                    <div class="text-sm truncate pr-1">{{ $memo->title }}</div>
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                        <button type="button"
                                onclick="event.stopPropagation(); trashMemo({{ $memo->id }})"
                                class="text-gray-400 hover:text-gray-200">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                @if($memo->category)
                    <div class="text-xs text-gray-400 mt-0.5 flex items-center">
                        <span class="w-2 h-2 rounded-full mr-1" style="background-color: {{ $memo->category->color }}"></span>
                        {{ $memo->category->name }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
