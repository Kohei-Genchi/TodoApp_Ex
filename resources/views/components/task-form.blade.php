@props(['categories', 'action' => route('todos.store'), 'todo' => null, 'method' => 'POST'])

<form action="{{ $action }}" method="POST" {{ $attributes }}>
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-4">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">タスク名<span class="text-red-500">*</span></label>
            {{-- 「nullセーフ演算子」（?->)を使用して、$todoがnullでない場合のみtitleプロパティにアクセス --}}
            <input type="text" id="title" name="title" required value="{{ $todo?->title }}"
                   class="w-full mt-1 p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="due_date" class="block text-sm font-medium text-gray-700">日付</label>
            <input type="date" id="due_date" name="due_date" value="{{ $todo?->due_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}"
                   class="w-full mt-1 p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="due_time" class="block text-sm font-medium text-gray-700">時間</label>
            <input type="time" id="due_time" name="due_time" value="{{ $todo?->due_time?->format('H:i') ?? now()->format('H:i') }}"
                   class="w-full mt-1 p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">カテゴリー</label>
            <select id="category_id" name="category_id"
                    class="w-full mt-1 p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                <option value="">カテゴリーなし</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                            data-color="{{ $category->color }}"
                            {{-- @selected ディレクティブ：条件が真の場合に "selected" 属性を追加 --}}
                            @selected($todo?->category_id == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="recurrence_type" class="block text-sm font-medium text-gray-700">繰り返し</label>
            <select id="recurrence_type" name="recurrence_type"
                    class="w-full mt-1 p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                <option value="none" @selected($todo?->recurrence_type == 'none' || !$todo?->recurrence_type)>繰り返しなし</option>
                <option value="daily" @selected($todo?->recurrence_type == 'daily')>毎日</option>
                <option value="weekly" @selected($todo?->recurrence_type == 'weekly')>毎週</option>
                <option value="monthly" @selected($todo?->recurrence_type == 'monthly')>毎月</option>
            </select>
        </div>
    {{-- タスクに繰り返しタイプがあり、かつ "none" でない場合にのみ表示（それ以外の場合は "hidden" クラスで非表示） --}}
        <div id="recurrence-end-container" class="{{ $todo?->recurrence_type && $todo?->recurrence_type !== 'none' ? '' : 'hidden' }}">
            <label for="recurrence_end_date" class="block text-sm font-medium text-gray-700">繰り返し終了日</label>
            <input type="date" id="recurrence_end_date" name="recurrence_end_date"
                   value="{{ $todo?->recurrence_end_date?->format('Y-m-d') }}"
                   class="w-full mt-1 p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    {{ $slot }}
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const recurrenceTypeSelect = document.getElementById('recurrence_type');
    const recurrenceEndContainer = document.getElementById('recurrence-end-container');

    if (recurrenceTypeSelect && recurrenceEndContainer) {
        recurrenceTypeSelect.addEventListener('change', function() {
            recurrenceEndContainer.classList.toggle('hidden', this.value === 'none');
        });
    }


    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        function updateCategoryBorder() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            categorySelect.style.borderLeft = selectedOption.value
                ? `4px solid ${selectedOption.dataset.color}`
                : '';
        }

        categorySelect.addEventListener('change', updateCategoryBorder);
        updateCategoryBorder();
    }
});
</script>
