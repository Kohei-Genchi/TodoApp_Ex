{{-- <!-- resources/views/components/calendar-navigation.blade.php -->
@props(['currentView' => 'day', 'date' => null])

@php
    $date = $date ?? now();
    $prevDayLink = route('todos.date', ['date' => $date->copy()->subDay()->format('Y-m-d')]);
    $nextDayLink = route('todos.date', ['date' => $date->copy()->addDay()->format('Y-m-d')]);
    $prevWeekLink = route('todos.week', ['start_date' => $date->copy()->subWeek()->startOfWeek()->format('Y-m-d')]);
    $nextWeekLink = route('todos.week', ['start_date' => $date->copy()->addWeek()->startOfWeek()->format('Y-m-d')]);
    $prevMonthLink = route('todos.month', ['month' => $date->copy()->subMonth()->month, 'year' => $date->copy()->subMonth()->year]);
    $nextMonthLink = route('todos.month', ['month' => $date->copy()->addMonth()->month, 'year' => $date->copy()->addMonth()->year]);
@endphp

<div class="flex justify-between items-center mb-6">
    <div class="flex items-center space-x-2">
        <!-- Previous button -->
        <a href="{{ $currentView === 'day' ? $prevDayLink : ($currentView === 'week' ? $prevWeekLink : $prevMonthLink) }}"
           class="p-1 rounded hover:bg-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
        </a>

        <!-- Title -->
        <div class="relative">
            <h2 class="text-xl font-bold flex items-center cursor-pointer hover:text-blue-600" id="calendarSelector">
                @if($currentView === 'day')
                    {{ $date->format('Y年n月j日') }}
                @elseif($currentView === 'week')
                    {{ $date->startOfWeek()->format('Y年n月j日') }} - {{ $date->endOfWeek()->format('n月j日') }}
                @else
                    {{ $date->format('Y年n月') }}
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </h2>

            <!-- Date selector dropdown -->
            <div id="calendarDropdown" class="hidden absolute z-10 mt-2 p-4 bg-white rounded-lg shadow-lg border border-gray-200">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">表示方法:</label>
                    <div class="flex space-x-2">
                        <a href="{{ route('todos.date', ['date' => $date->format('Y-m-d')]) }}"
                           class="px-3 py-1 {{ $currentView === 'day' ? 'bg-blue-500 text-white' : 'bg-gray-200' }} rounded hover:bg-blue-600 hover:text-white">
                            日
                        </a>
                        <a href="{{ route('todos.week', ['start_date' => $date->copy()->startOfWeek()->format('Y-m-d')]) }}"
                           class="px-3 py-1 {{ $currentView === 'week' ? 'bg-blue-500 text-white' : 'bg-gray-200' }} rounded hover:bg-blue-600 hover:text-white">
                            週
                        </a>
                        <a href="{{ route('todos.month', ['month' => $date->month, 'year' => $date->year]) }}"
                           class="px-3 py-1 {{ $currentView === 'month' ? 'bg-blue-500 text-white' : 'bg-gray-200' }} rounded hover:bg-blue-600 hover:text-white">
                            月
                        </a>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">日付を選択:</label>
                    <input type="date" id="datePicker" value="{{ $date->format('Y-m-d') }}"
                           class="p-2 border border-gray-300 rounded w-full">
                </div>

                <div class="flex justify-between">
                    <a href="{{ $currentView === 'day' ? route('todos.date', ['date' => now()->format('Y-m-d')]) :
                              ($currentView === 'week' ? route('todos.week', ['start_date' => now()->startOfWeek()->format('Y-m-d')]) :
                              route('todos.month', ['month' => now()->month, 'year' => now()->year])) }}"
                       class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                        今日
                    </a>
                    <button id="applyDateButton" class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600">
                        変更
                    </button>
                </div>
            </div>
        </div>

        <!-- Next button -->
        <a href="{{ $currentView === 'day' ? $nextDayLink : ($currentView === 'week' ? $nextWeekLink : $nextMonthLink) }}"
           class="p-1 rounded hover:bg-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </a>
    </div>

    <div class="flex space-x-2">
        <a href="{{ route('todos.date', ['date' => now()->format('Y-m-d')]) }}"
           class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100">
            今日
        </a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarSelector = document.getElementById('calendarSelector');
        const calendarDropdown = document.getElementById('calendarDropdown');
        const datePicker = document.getElementById('datePicker');
        const applyDateButton = document.getElementById('applyDateButton');

        // Toggle dropdown
        calendarSelector?.addEventListener('click', function() {
            calendarDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!calendarSelector?.contains(e.target) && !calendarDropdown?.contains(e.target)) {
                calendarDropdown?.classList.add('hidden');
            }
        });

        // Apply date button
        applyDateButton?.addEventListener('click', function() {
            const selectedDate = datePicker.value;
            if (selectedDate) {
                @if($currentView === 'day')
                    window.location.href = `{{ route('todos.date') }}?date=${selectedDate}`;
                @elseif($currentView === 'week')
                    window.location.href = `{{ route('todos.week') }}?start_date=${selectedDate}`;
                @else
                    const date = new Date(selectedDate);
                    const month = date.getMonth() + 1;
                    const year = date.getFullYear();
                    window.location.href = `{{ route('todos.month') }}?month=${month}&year=${year}`;
                @endif
            }
        });

        // Enter key on date picker
        datePicker?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                applyDateButton.click();
            }
        });
    });
</script>
@endpush --}}
