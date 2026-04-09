<x-paystack-demo.layout title="Refunds Demo" heading="Refunds"
    description="Create, retry, fetch, and list refunds." :pages="$pages" :result="$result ?? null"
    :result-label="$resultLabel ?? null" current-path="/paystack/demo/refunds">
    <section class="border border-slate-200 bg-white p-5">
        <div class="grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/refunds" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="create">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Create refund</p>
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-slate-700">Transaction
                        <input name="transaction" type="text" placeholder="T685312322670591"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Amount
                        <input name="amount" type="number" step="1" min="0" placeholder="10000"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Currency
                        <input name="currency" type="text" placeholder="NGN"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Customer note
                        <input name="customer_note" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Merchant note
                        <input name="merchant_note" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button class="mt-4 w-full bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">Create refund</button>
            </form>

            <form method="post" action="/paystack/demo/refunds" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="fetch">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Fetch refund</p>
                <label class="block text-sm font-medium text-slate-700">Refund ID
                    <input name="refund_id" type="text" placeholder="1234567"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Fetch refund</button>
            </form>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/refunds" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="list">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">List refunds</p>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700">Transaction filter
                        <input name="transaction" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Currency filter
                        <input name="currency" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">From
                        <input name="from" type="text" placeholder="2026-01-01"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">To
                        <input name="to" type="text" placeholder="2026-12-31"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Per page
                        <input name="per_page" type="number" value="10"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Page
                        <input name="page" type="number" value="1"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">List refunds</button>
            </form>

            <form method="post" action="/paystack/demo/refunds" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="retry">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Retry refund</p>
                <label class="block text-sm font-medium text-slate-700">Refund ID
                    <input name="refund_id" type="text" placeholder="1234567"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    <label class="block text-sm font-medium text-slate-700">Currency
                        <input name="refund_currency" type="text" value="NGN"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Account number
                        <input name="account_number" type="text" placeholder="1234567890"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Bank ID
                        <input name="bank_id" type="text" placeholder="9"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Retry refund</button>
            </form>
        </div>
    </section>
</x-paystack-demo.layout>
