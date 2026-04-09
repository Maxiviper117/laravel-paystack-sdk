<x-paystack-demo.layout
    title="Billing Layer Demo"
    heading="Billing layer"
    description="Test the optional Billable trait and the local customer/subscription tables."
    :pages="$pages"
    :result="$result ?? null"
    :result-label="$resultLabel ?? null"
    current-path="/paystack/demo/billing-layer"
>
    <section class="border border-slate-200 bg-white p-5">
        <div class="grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/billing-layer" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="sync">
                <label class="block text-sm font-medium text-slate-700">Email
                    <input name="email" type="email" value="billable@example.com" class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Name
                    <input name="name" type="text" value="Billable Demo User" class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button class="mt-4 bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">Sync customer</button>
            </form>

            <form method="post" action="/paystack/demo/billing-layer" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="create-subscription">
                <label class="block text-sm font-medium text-slate-700">Email
                    <input name="email" type="email" value="billable@example.com" class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Plan code
                    <input name="plan" type="text" value="PLN_123" class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Subscription name
                    <input name="subscription_name" type="text" value="default" class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Create subscription</button>
            </form>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/billing-layer" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="enable">
                <label class="block text-sm font-medium text-slate-700">Subscription name
                    <input name="subscription_name" type="text" value="default" class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Enable</button>
            </form>

            <form method="post" action="/paystack/demo/billing-layer" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="disable">
                <label class="block text-sm font-medium text-slate-700">Subscription name
                    <input name="subscription_name" type="text" value="default" class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Disable</button>
            </form>
        </div>
    </section>
</x-paystack-demo.layout>
