<nav class="bg-gray-800 text-white h-full w-64 fixed left-0 top-0 overflow-y-auto">
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="p-4">
@auth
<div class="flex justify-between items-center cursor-pointer p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" onclick="toggleUserDropdown()">
    <div class="flex items-center">
        <!-- User icon before username -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <span class="font-bold">{{ Auth::user()->name }}</span>
    </div>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
</div>

    <!-- Dropdown Menu - Hidden by default -->
    <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-gray-700 rounded-md shadow-lg z-10 hidden border border-gray-600">
        <div class="py-1">
            <a href="{{ route('home') }}" class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Home
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profile
            </a>
            <a href="{{ route('stripe.subscription') }}" class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9a2 2 0 10-4 0v5a2 2 0 104 0V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6m-6 6h6" />
                </svg>
                Subscription
            </a>
            <button onclick="confirmLogout()" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-200 hover:bg-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </button>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
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
</div>
</div>
@endauth

        @auth
        {{-- Check if the current route is not profile or subscription related --}}
        @if(!Request::is('profile*') && !Request::is('stripe/subscription*'))
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
            {{ Auth::user()->todos()->whereNull('due_date')->where('status', 'pending')->count() }}
            </div>
            </div>

                <div class="memo-list-container sidebar-memo-list space-y-1 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                @php
                $memos = Auth::user()->todos()
                ->with('category')
                ->whereNull('due_date')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
                @endphp

                    @if($memos->isEmpty())
                    <div class="text-xs text-gray-500 text-center py-2">メモはありません</div>
                    @else
                    @foreach($memos as $memo)
                    <div class="group bg-gray-700 hover:bg-gray-600 rounded py-1.5 px-2 cursor-pointer transition-colors"
                    onclick="editTodo({{ $memo->id }}, {{ json_encode([
                    'id' => $memo->id,
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
                    <!-- Replace form with button that calls JavaScript -->
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
                    </div>
                    @endif
                    @else
                    <div class="mt-6 p-3 bg-gray-700 rounded text-xs text-gray-300">
                    <p>タスク管理機能を使用するには、ログインまたは新規登録してください。</p>
                    </div>
                    @endauth
                    </div>
                    <script>
                    // Function to trash a memo using the API
                    function trashMemo(id) {
                    // Create a form and submit it via POST
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/todos/${id}/trash`;

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            // Add method override for PATCH
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            form.appendChild(methodInput);

            // Append form to document and submit
            document.body.appendChild(form);
            form.submit();
            }

            // Toggle user dropdown visibility
            function toggleUserDropdown() {
                const dropdown = document.getElementById('userDropdown');
                dropdown.classList.toggle('hidden');
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.relative.mb-6');
            const dropdown = document.querySelector('#user-dropdown'); // Update this selector to match your actual dropdown ID

            if (dropdown && userMenu &&
                !dropdown.contains(event.target) &&
                !userMenu.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
            });

            // Function to confirm logout
            function confirmLogout() {
                if (confirm('ログアウトしてもよろしいですか？')) {
                    document.getElementById('logout-form').submit();
                }
            }
            </script>
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
