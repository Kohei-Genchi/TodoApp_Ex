<div class="border-b border-gray-200 mb-6">
    <nav class="flex space-x-4">

        <a href="{{ route('todos.index', ['view' => 'today']) }}"
           class="px-3 py-2 text-sm font-medium {{ request()->input('view') === 'today' || (request()->routeIs('todos.index') && !request()->has('view')) ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            今日のタスク
        </a>

    </nav>
</div>
