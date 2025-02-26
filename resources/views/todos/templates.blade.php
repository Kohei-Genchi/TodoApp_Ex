<!-- resources/views/todos/templates.blade.php -->
<x-app-layout>
    <div class="max-w-4xl mx-auto p-4">
        <div class="mb-6">
            <h1 class="text-xl font-semibold">テンプレート</h1>
        </div>


        <div class="mb-6 bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-lg font-medium mb-4">新しいテンプレート</h2>

            <form action="{{ route('todos.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">テンプレート名</label>
                    <input type="text" id="title" name="title" required placeholder="テンプレート名を入力"
                           class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="todo_type" class="block text-sm font-medium text-gray-700 mb-1">繰り返しタイプ</label>
                    <select id="todo_type" name="recurrence_type"
                            class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="daily">毎日</option>
                        <option value="weekly">毎週</option>
                        <option value="monthly">毎月</option>
                    </select>
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">カテゴリー</label>
                    <select id="category_id" name="category_id"
                            class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">カテゴリーなし</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" data-color="{{ $category->color }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="location" value="TEMPLATE">
                <input type="hidden" name="due_date" value="{{ now()->format('Y-m-d') }}">

                <div>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        テンプレートを作成
                    </button>
                </div>
            </form>
        </div>


        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-lg font-medium mb-4">テンプレート一覧</h2>

            @forelse ($templates as $template)
                <div class="py-3 border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-3"
                                 style="background-color: {{ $template->category ? $template->category->color : '#ddd' }}"></div>
                            <span class="font-medium">{{ $template->title }}</span>
                            <span class="ml-2 text-sm text-gray-500">
                                @switch($template->recurrence_type)
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
                        </div>

                        <div class="flex space-x-2">

                            <form action="{{ route('todos.store') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="title" value="{{ $template->title }}">
                                <input type="hidden" name="recurrence_type" value="{{ $template->recurrence_type }}">
                                <input type="hidden" name="category_id" value="{{ $template->category_id }}">
                                <input type="hidden" name="due_date" value="{{ now()->format('Y-m-d') }}">
                                <button type="submit" class="text-sm text-blue-500 hover:text-blue-700">
                                    本日のタスクに追加
                                </button>
                            </form>


                            <form action="{{ route('todos.destroy', $template) }}" method="POST" class="inline"
                                  onsubmit="return confirm('このテンプレートを削除しますか？')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:text-red-700">
                                    削除
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">テンプレートはありません</p>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Category color previews
            const categorySelect = document.getElementById('category_id');
            categorySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const color = selectedOption.dataset.color;
                    this.style.borderLeft = `4px solid ${color}`;
                } else {
                    this.style.borderLeft = '';
                }
            });
        });
    </script>
</x-app-layout>
