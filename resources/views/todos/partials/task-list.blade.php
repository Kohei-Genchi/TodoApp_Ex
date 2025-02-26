
    @auth

    @if($todos->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg shadow">
            <p class="text-gray-500">タスクはありません</p>
        </div>
    @else
        <ul class="bg-white rounded-lg shadow divide-y divide-gray-200">
            @foreach($todos as $todo)
                <li class="hover:bg-gray-50 transition-colors"
                    style="border-left: 4px solid {{ $todo->category ? $todo->category->color : 'transparent' }}">
                    <div class="flex items-center p-4">

                        <form id="toggle-form-{{ $todo->id }}" action="{{ route('todos.toggle', $todo) }}" method="POST" class="mr-3">
                            @csrf
                            @method('PATCH')
                            <input type="checkbox"
                                   onchange="this.form.submit()"
                                   {{ $todo->status === 'completed' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 rounded focus:ring-blue-500">
                        </form>


                        <div class="flex-1 min-w-0" onclick="editTodo({{ $todo->id }}, {{ json_encode([
                            'title' => $todo->title,
                            'due_date' => $todo->due_date ? $todo->due_date->format('Y-m-d') : null,
                            'due_time' => $todo->due_time ? $todo->due_time->format('H:i') : null,
                            'category_id' => $todo->category_id,
                            'recurrence_type' => $todo->recurrence_type,
                            'recurrence_end_date' => $todo->recurrence_end_date ? $todo->recurrence_end_date->format('Y-m-d') : null,
                        ]) }})" style="cursor: pointer">
                            <p class="text-sm font-medium text-gray-900 truncate {{ $todo->status === 'completed' ? 'line-through' : '' }}">
                                {{ $todo->title }}
                            </p>

                            @if($todo->due_date || $todo->category)
                                <div class="flex items-center text-xs text-gray-500 mt-0.5">
                                    @if($todo->due_date)
                                        <span class="mr-2">
                                            {{ $todo->due_date->format('Y/m/d') }}
                                            @if($todo->due_time)
                                                {{ $todo->due_time->format('H:i') }}
                                            @endif
                                        </span>
                                    @endif

                                    @if($todo->category)
                                        <span class="inline-flex items-center">
                                            <span class="w-2 h-2 rounded-full mr-1" style="background-color: {{ $todo->category->color }}"></span>
                                            {{ $todo->category->name }}
                                        </span>
                                    @endif

                                    @if($todo->isRecurring())
                                        <span class="ml-2">
                                            {{ $todo->recurrence_type === 'daily' ? '毎日' :
                                               ($todo->recurrence_type === 'weekly' ? '毎週' :
                                               ($todo->recurrence_type === 'monthly' ? '毎月' : '')) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>


                        <div class="flex-shrink-0 ml-2">
                            <form action="{{ route('todos.trash', $todo) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@else

    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="mb-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900">タスク管理機能へようこそ</h3>

        <div class="mt-6 flex justify-center space-x-4">
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                ログイン
            </a>
            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                新規登録
            </a>
        </div>
    </div>
@endauth
