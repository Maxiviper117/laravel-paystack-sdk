<x-paystack-demo.layout
    title="Billing Layer Demo"
    heading="Billing layer"
    description="Test the optional Billable trait and the local customer/subscription tables."
    :pages="$pages"
    :result="$result ?? null"
    :result-label="$resultLabel ?? null"
    current-path="/paystack/demo/billing-layer"
>
    <section class="rounded-3xl border border-slate-200 bg-white p-6">
        <h2 class="text-2xl font-bold text-slate-900">Billing layer</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Test the optional Billable trait and the local customer/subscription tables.</p>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/billing-layer" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="sync">
                <label class="block text-sm font-medium text-slate-700">Email
                    <input name="email" type="email" value="billable@example.com" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Name
                    <input name="name" type="text" value="Billable Demo User" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <button class="mt-4 rounded-xl bg-orange-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-orange-300">Sync customer</button>
            </form>

            <form method="post" action="/paystack/demo/billing-layer" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="create-subscription">
                <label class="block text-sm font-medium text-slate-700">Email
                    <input name="email" type="email" value="billable@example.com" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Plan code
                    <input name="plan" type="text" value="PLN_123" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Subscription name
                    <input name="subscription_name" type="text" value="default" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">Create subscription</button>
            </form>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/billing-layer" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="enable">
                <label class="block text-sm font-medium text-slate-700">Subscription name
                    <input name="subscription_name" type="text" value="default" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">Enable</button>
            </form>

            <form method="post" action="/paystack/demo/billing-layer" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="disable">
                <label class="block text-sm font-medium text-slate-700">Subscription name
                    <input name="subscription_name" type="text" value="default" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">Disable</button>
            </form>
        </div>
    </section>
</x-paystack-demo.layout>
