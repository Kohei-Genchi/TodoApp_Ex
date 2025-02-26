<!-- resources/views/layouts/navigation.blade.php -->
<nav class="bg-gray-800 text-white h-full w-64 fixed left-0 top-0 overflow-y-auto">
    <div class="p-4">
        @auth

            <div class="flex justify-between items-center mb-6">
                <span class="font-bold">{{ Auth::user()->name }}</span>
                <div class="flex space-x-3">
                    <a href="{{ route('categories.index') }}" class="text-lg text-gray-400 hover:text-white">
                        ⚙️
                    </a>
                    <a href="{{ route('todos.index', ['view' => 'calendar']) }}" class="text-lg text-gray-400 hover:text-white">
                        🗓️
                    </a>
                    <a href="{{ route('todos.trashed') }}" class="text-lg text-gray-400 hover:text-white">
                        🗑️
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-lg text-gray-400 hover:text-white">
                            🚪
                        </button>
                    </form>
                </div>
            </div>
        @else

            <div class="flex justify-between items-center mb-6">
                <span class="font-bold">ゲスト</span>
                <div class="flex space-x-3">
                    <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white">
                        🔑
                    </a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-400 hover:text-white">
                        📝
                    </a>
                </div>
            </div>
        @endauth


        <div class="mb-6">
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-2">
                ToDo管理
            </div>
            <ul class="space-y-1">

                <li>
                    <a href="{{ route('todos.index', ['view' => 'inbox']) }}"
                       class="block px-3 py-2 rounded hover:bg-gray-700 text-xs
                              {{ request()->input('view') === 'inbox' ? 'bg-gray-700' : '' }}">
                        INBOX
                        @auth
                            <span class="text-xs bg-gray-600 px-1 rounded">{{ Auth::user()->todos()->where('location', 'INBOX')->count() }}</span>
                        @endauth
                    </a>
                </li>
            </ul>
        </div>

        @auth

            <div class="mb-6">
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-2">
                    クイック入力
                </div>
                <form action="{{ route('todos.store') }}" method="POST">
                    @csrf
                    <div class="flex items-center bg-gray-700 rounded overflow-hidden">
                        <input type="text" name="title" required placeholder="新しいタスク"
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
        @else

            <div class="mt-6 p-3 bg-gray-700 rounded text-xs text-gray-300">
                <p>タスク管理機能を使用するには、ログインまたは新規登録してください。</p>
            </div>
        @endauth
    </div>
</nav>
