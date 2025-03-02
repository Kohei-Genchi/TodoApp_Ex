<!-- resources/views/layouts/navigation.blade.php -->
<nav class="bg-gray-800 text-white h-full w-64 fixed left-0 top-0 overflow-y-auto">
    <div class="p-4">
        @auth
            <div class="flex justify-between items-center mb-6">
                <span class="font-bold">{{ Auth::user()->name }}</span>
                <div class="flex space-x-3">
                    <a href="{{ route('todos.index', ['view' => 'calendar']) }}" class="text-lg text-gray-400 hover:text-white" title="カレンダー">
                        🗓️
                    </a>
                    <a href="{{ route('todos.trashed') }}" class="text-lg text-gray-400 hover:text-white" title="ゴミ箱">
                        🗑️
                    </a>
                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('ログアウトしますか？');">
                        @csrf
                        <button type="submit" class="text-lg text-gray-400 hover:text-white" title="ログアウト">
                            🚪
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="flex justify-between items-center mb-6">
                <span class="font-bold">ゲスト</span>
                <div class="flex space-x-3">
                    <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white" title="ログイン">
                        🔑
                    </a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-400 hover:text-white" title="新規登録">
                        📝
                    </a>
                    <a href="{{ route('guest.login') }}" class="text-sm text-gray-400 hover:text-white" title="かんたんログイン">
                        👤
                    </a>
                </div>
            </div>
        @endauth

        @auth
            <!-- クイック入力セクション -->
            <div class="mb-4">
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-2">
                    クイック入力
                </div>
                <form action="{{ route('todos.store') }}" method="POST">
                    @csrf
                    <div class="flex items-center bg-gray-700 rounded overflow-hidden">
                        <input type="text" name="title" required placeholder="新しいメモを入力"
                               class="w-full bg-gray-700 px-3 py-2 text-sm focus:outline-none text-white">
                        <input type="hidden" name="location" value="INBOX">
                        <button type="submit" class="px-2 py-2 text-gray-400 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- MEMOリスト -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="text-xs text-gray-400 uppercase tracking-wider">メモ一覧</div>
                    <div class="text-xs bg-gray-600 px-1.5 py-0.5 rounded-full">
                        {{ Auth::user()->todos()->where('location', 'INBOX')->count() }}
                    </div>
                </div>

                <div class="space-y-1 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                    @php
                        $memos = Auth::user()->todos()
                            ->with('category')
                            ->where('location', 'INBOX')
                            ->orderBy('created_at', 'desc')
                            ->get();
                    @endphp

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
                                        <form action="{{ route('todos.trash', $memo) }}" method="POST" class="inline" onclick="event.stopPropagation();">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-gray-400 hover:text-gray-200">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
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
            </div>
        @else
            <div class="mt-6 p-3 bg-gray-700 rounded text-xs text-gray-300">
                <p>タスク管理機能を使用するには、ログインまたは新規登録してください。</p>
            </div>
        @endauth
    </div>
</nav>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(55, 65, 81, 0.5);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.8);
}
</style>
