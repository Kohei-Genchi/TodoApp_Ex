<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">


                    <div class="mt-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">サブスク申請</h2>
                        <p class="mb-4">サブスクリプションの申請ページです。チェックアウトに進んでお支払い手続きを完了してください。</p>
                        <a href="{{route('stripe.subscription.checkout')}}"
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            チェックアウトページに進む
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
