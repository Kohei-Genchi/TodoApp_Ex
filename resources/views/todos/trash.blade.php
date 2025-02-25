<x-app-layout>
    <div class="max-w-6xl mx-auto p-4">

        @include('todos.partials.nav-tabs')


        <div>
            @if($todos->isEmpty())
                <div class="text-center py-12 bg-white rounded-lg shadow">
                    <p class="text-gray-500">ゴミ箱は空です</p>
                </div>
            @else
                <ul class="bg-white rounded-lg shadow divide-y divide-gray-200">
                    @foreach($todos as $todo)
                        <li class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="sm:flex sm:items-center sm:justify-between">
                                <div class="mb-2 sm:mb-0">
                                    <div class="flex items-center">
                                        @if($todo->category)
                                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $todo->category->color }}"></div>
                                        @endif
                                        <p class="font-medium text-gray-400 line-through">{{ $todo->title }}</p>
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        @if($todo->due_date)
                                            <span>期限: {{ $todo->due_date->format('Y/m/d') }}</span>
                                            @if($todo->due_time)
                                                <span>{{ $todo->due_time->format('H:i') }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-2 sm:mt-0 flex space-x-2">

                                    <form action="{{ route('todos.update', $todo) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="pending">
                                        <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                                            復元
                                        </button>
                                    </form>


                                    <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('このタスクを完全に削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                                            削除
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
