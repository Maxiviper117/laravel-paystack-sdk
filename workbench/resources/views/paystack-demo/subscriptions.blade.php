<x-paystack-demo.layout
    title="Subscriptions Demo"
    heading="Subscriptions"
    description="Create, fetch, list, enable, and disable subscriptions."
    :pages="$pages"
    :result="$result ?? null"
    :result-label="$resultLabel ?? null"
    current-path="/paystack/demo/subscriptions"
>
    <section class="rounded-3xl border border-slate-200 bg-white p-6">
        <h2 class="text-2xl font-bold text-slate-900">Subscriptions</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Create, fetch, list, enable, and disable subscriptions.</p>

        <form method="post" action="/paystack/demo/subscriptions" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            @csrf
            <input type="hidden" name="action" value="create">
            <div class="grid gap-3 md:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">Customer code
                    <input name="customer" type="text" value="CUS_123" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <label class="block text-sm font-medium text-slate-700">Plan code
                    <input name="plan" type="text" value="PLN_123" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <label class="block text-sm font-medium text-slate-700">Authorization
                    <input name="authorization" type="text" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <label class="block text-sm font-medium text-slate-700">Start date
                    <input name="start_date" type="text" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
            </div>
            <button class="mt-4 rounded-xl bg-orange-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-orange-300">Create subscription</button>
        </form>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/subscriptions" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="fetch">
                <label class="block text-sm font-medium text-slate-700">Subscription identifier
                    <input name="subscription_identifier" type="text" placeholder="SUB_123" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">Fetch</button>
            </form>

            <form method="post" action="/paystack/demo/subscriptions" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="list">
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700">Customer filter
                        <input name="customer" type="text" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Plan filter
                        <input name="plan" type="text" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Per page
                        <input name="per_page" type="number" value="10" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Page
                        <input name="page" type="number" value="1" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                </div>
                <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">List</button>
            </form>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/subscriptions" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="enable">
                <label class="block text-sm font-medium text-slate-700">Code
                    <input name="code" type="text" placeholder="SUB_123" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Token
                    <input name="token" type="text" placeholder="email token" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">Enable</button>
            </form>

            <form method="post" action="/paystack/demo/subscriptions" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="disable">
                <label class="block text-sm font-medium text-slate-700">Code
                    <input name="code" type="text" placeholder="SUB_123" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Token
                    <input name="token" type="text" placeholder="email token" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </label>
                <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">Disable</button>
            </form>
        </div>
    </section>
</x-paystack-demo.layout>
