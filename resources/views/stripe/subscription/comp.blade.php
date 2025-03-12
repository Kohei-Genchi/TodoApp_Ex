<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">


                    <div class="mt-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">支払い完了</h2>
                        <p class="mb-4">サブスクの支払いが完了しました。</p>
                        <a href="{{ route('stripe.subscription.customer_portal') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            カスタマーポータルに進む
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
