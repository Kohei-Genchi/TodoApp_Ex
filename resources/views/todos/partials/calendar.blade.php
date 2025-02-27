
@auth

    <div class="bg-white rounded-lg shadow overflow-hidden">

        <div class="grid grid-cols-7 text-center text-xs text-gray-700 bg-gray-100">
            <div class="py-1 text-red-600">日</div>
            <div class="py-1">月</div>
            <div class="py-1">火</div>
            <div class="py-1">水</div>
            <div class="py-1">木</div>
            <div class="py-1">金</div>
            <div class="py-1 text-blue-600">土</div>
        </div>


        <div class="grid grid-cols-7 border-t border-l border-gray-200">
            @php
                //表示月の1日
                $startMonth = $date->copy()->startOfMonth();
                //表示月の末日
                $endMonth = $date->copy()->endOfMonth();

                //カレンダー表示の最初の日（通常は月の初日を含む週の日曜日）
                $firstDayOfView = $startMonth->copy()->startOfWeek();
                if ($firstDayOfView->month != $startMonth->month) {


                    $firstDayOfView = $startMonth->copy()->subDay();
                }

                //カレンダー表示の最後の日（通常は月の末日を含む週の土曜日）
                $lastDayOfView = $endMonth->copy()->endOfWeek();
                if ($lastDayOfView->month != $endMonth->month) {


                    $lastDayOfView = $endMonth->copy()->addDay();
                }

                $today = now()->startOfDay();


                $days = [];
                for ($day = $firstDayOfView->copy(); $day <= $lastDayOfView; $day->addDay()) {

                    if ($day->month == $startMonth->month ||
                        $day->isSameDay($firstDayOfView) ||
                        $day->isSameDay($lastDayOfView)) {
                        $days[] = $day->copy();
                    }
                }


                $startDayOfWeek = $days[0]->dayOfWeek;

                // 月初の前に表示する空白セルの数
                $emptyBefore = $startDayOfWeek;

                $endDayOfWeek = end($days)->dayOfWeek;
                // 月末の後に表示する空白セルの数
                $emptyAfter = 6 - $endDayOfWeek;
            @endphp


            @for ($i = 0; $i < $emptyBefore; $i++)
                <div class="min-h-28 p-1 border-r border-b border-gray-200 bg-gray-50"></div>
            @endfor


            @foreach ($days as $day)
                @php
                    $isCurrentMonth = $day->month === $date->month;
                    $isToday = $day->isSameDay($today);
                    // その日に設定されたタスクのコレクション
                    $dayTodos = $todos->filter(function($todo) use ($day) {
                        return $todo->due_date && $todo->due_date->isSameDay($day);
                    });
                @endphp

                <div class="min-h-28 p-1 border-r border-b border-gray-200
                            {{ $isCurrentMonth ? 'bg-white' : 'bg-gray-50' }}
                            {{ $isToday ? 'bg-yellow-50' : '' }}">


                    <a href="{{ route('todos.index', ['view' => 'date', 'date' => $day->format('Y-m-d')]) }}"
                       class="block text-center py-0.5 {{ $isToday ? 'font-bold bg-yellow-100 rounded-full w-5 h-5 mx-auto' : '' }}
                              {{ !$isCurrentMonth ? 'text-gray-400' : '' }}
                              {{ $day->dayOfWeek === 0 ? 'text-red-600' : '' }}
                              {{ $day->dayOfWeek === 6 ? 'text-blue-600' : '' }}">
                        {{ $day->day }}
                    </a>


                    <div class="mt-0.5 text-xs space-y-0.5 max-h-16 overflow-y-auto">
                        @foreach ($dayTodos->take(2) as $todo)
                            <div class="flex items-center px-1 rounded hover:bg-gray-100
                                        {{ $todo->status === 'completed' ? 'text-gray-400 line-through' : '' }}"
                                 style="border-left: 2px solid {{ $todo->category ? $todo->category->color : '#ddd' }}">
                                <form action="{{ route('todos.toggle', $todo) }}" method="POST" class="mr-0.5 flex-shrink-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="checkbox" onChange="this.form.submit()"
                                           {{ $todo->status === 'completed' ? 'checked' : '' }}
                                           class="w-2 h-2">
                                </form>
                                <span class="truncate cursor-pointer text-xs" onclick="editTodo({{ $todo->id }}, {{ json_encode([
                                    'title' => $todo->title,
                                    'due_date' => $todo->due_date ? $todo->due_date->format('Y-m-d') : null,
                                    'due_time' => $todo->due_time ? $todo->due_time->format('H:i') : null,
                                    'category_id' => $todo->category_id,
                                    'recurrence_type' => $todo->recurrence_type,
                                    'recurrence_end_date' => $todo->recurrence_end_date ? $todo->recurrence_end_date->format('Y-m-d') : null,
                                ]) }})">
                                    {{ $todo->title }}
                                </span>
                            </div>
                        @endforeach

                        @if ($dayTodos->count() > 2)
                            <div class="text-xs text-blue-500 text-center">
                                <a href="{{ route('todos.index', ['view' => 'date', 'date' => $day->format('Y-m-d')]) }}" class="text-xs">
                                    +{{ $dayTodos->count() - 2 }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach


            @for ($i = 0; $i < $emptyAfter; $i++)
                <div class="min-h-28 p-1 border-r border-b border-gray-200 bg-gray-50"></div>
            @endfor
        </div>
    </div>
@else

    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="mb-4">
            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900">カレンダービュー</h3>
        <p class="mt-2 text-sm text-gray-500">
            カレンダービューでタスクを表示するには、ログインまたは新規登録してください。
        </p>
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
