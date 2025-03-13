<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Subscription</h1>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                サブスク申請
            </h2>
            <p class="mb-4">
                サブスクリプションの申請ページです。チェックアウトに進んでお支払い手続きを完了してください。
            </p>
            <a
                :href="checkoutUrl"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
            >
                チェックアウトページに進む
            </a>
        </div>
    </div>
</template>

<script>
export default {
    name: "Subscription",

    data() {
        return {
            checkoutUrl: "/stripe/subscription/checkout",
        };
    },
};
</script>
