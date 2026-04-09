<x-paystack-demo.layout
    title="Transactions Demo"
    heading="Transactions"
    description="Initialize a checkout or verify a returned reference."
    :pages="$pages"
    :result="$result ?? null"
    :result-label="$resultLabel ?? null"
    current-path="/paystack/demo/transactions"
>
    <section class="rounded-3xl border border-slate-200 bg-white p-6">
        <h2 class="text-2xl font-bold text-slate-900">Transactions</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Initialize a checkout or verify a returned reference.</p>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/transactions" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="initialize">
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-slate-700">Email
                        <input name="email" type="email" value="customer@example.com" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Amount
                        <input name="amount" type="number" step="0.01" value="15.50" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Callback URL
                        <input name="callback_url" type="text" value="{{ url('/paystack/demo/transactions') }}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                </div>
                <button class="mt-4 w-full rounded-xl bg-orange-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-orange-300">Initialize</button>
            </form>

            <form method="post" action="/paystack/demo/transactions" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="verify">
                <label class="block text-sm font-medium text-slate-700">Reference
                    <input name="reference" type="text" placeholder="reference" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <button class="mt-4 w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">Verify</button>
            </form>
        </div>
    </section>
</x-paystack-demo.layout>
