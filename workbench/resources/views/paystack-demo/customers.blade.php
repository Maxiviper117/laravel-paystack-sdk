<x-paystack-demo.layout
    title="Customers Demo"
    heading="Customers"
    description="Create, update, and list customer records."
    :pages="$pages"
    :result="$result ?? null"
    :result-label="$resultLabel ?? null"
    current-path="/paystack/demo/customers"
>
    <section class="rounded-3xl border border-slate-200 bg-white p-6">
        <h2 class="text-2xl font-bold text-slate-900">Customers</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Create, update, and list customer records.</p>

        <div class="mt-4 grid gap-4">
            <form method="post" action="/paystack/demo/customers" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="action" value="create">
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Email
                        <input name="email" type="email" value="customer@example.com" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">First name
                        <input name="first_name" type="text" value="Jane" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Last name
                        <input name="last_name" type="text" value="Doe" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Phone
                        <input name="phone" type="text" value="+27123456789" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                </div>
                <button class="mt-4 rounded-xl bg-orange-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-orange-300">Create</button>
            </form>

            <div class="grid gap-4 md:grid-cols-2">
                <form method="post" action="/paystack/demo/customers" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    @csrf
                    <input type="hidden" name="action" value="update">
                    <label class="block text-sm font-medium text-slate-700">Customer code
                        <input name="customer_code" type="text" placeholder="CUS_123" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </label>
                    <div class="mt-3 grid gap-3">
                        <label class="block text-sm font-medium text-slate-700">Email
                            <input name="email" type="email" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">First name
                            <input name="first_name" type="text" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Last name
                            <input name="last_name" type="text" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                        </label>
                    </div>
                    <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">Update</button>
                </form>

                <form method="post" action="/paystack/demo/customers" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    @csrf
                    <input type="hidden" name="action" value="list">
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700">Per page
                            <input name="per_page" type="number" value="10" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Page
                            <input name="page" type="number" value="1" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                        </label>
                        <label class="block text-sm font-medium text-slate-700 md:col-span-2">Email filter
                            <input name="list_email" type="email" placeholder="Optional" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                        </label>
                    </div>
                    <button class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-slate-200">List</button>
                </form>
            </div>
        </div>
    </section>
</x-paystack-demo.layout>
