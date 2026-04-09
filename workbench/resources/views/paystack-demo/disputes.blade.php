<x-paystack-demo.layout title="Disputes Demo" heading="Disputes"
    description="List, fetch, update, resolve, and export disputes, plus upload and evidence helpers."
    :pages="$pages" :result="$result ?? null" :result-label="$resultLabel ?? null"
    current-path="/paystack/demo/disputes">
    <section class="border border-slate-200 bg-white p-5">
        <div class="grid gap-4 lg:grid-cols-2">
            <form method="post" action="/paystack/demo/disputes" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="list">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">List disputes</p>
                <div class="grid gap-3 md:grid-cols-2">
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
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Transaction filter
                        <input name="transaction" type="text" placeholder="5991760"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Status
                        <select name="status"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none">
                            <option value="">Any status</option>
                            <option value="awaiting-merchant-feedback">awaiting-merchant-feedback</option>
                            <option value="awaiting-bank-feedback">awaiting-bank-feedback</option>
                            <option value="pending">pending</option>
                            <option value="resolved">resolved</option>
                        </select>
                    </label>
                </div>
                <button class="mt-4 bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">
                    List disputes
                </button>
            </form>

            <form method="post" action="/paystack/demo/disputes" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="fetch">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Fetch dispute</p>
                <label class="block text-sm font-medium text-slate-700">Dispute identifier
                    <input name="dispute_identifier" type="text" placeholder="2867"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Fetch dispute
                </button>
            </form>

            <form method="post" action="/paystack/demo/disputes" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="transaction">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">List transaction disputes</p>
                <label class="block text-sm font-medium text-slate-700">Transaction identifier
                    <input name="transaction_identifier" type="text" placeholder="5991760"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    List transaction disputes
                </button>
            </form>

            <form method="post" action="/paystack/demo/disputes" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="update">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Update dispute</p>
                <div class="grid gap-3">
                    <label class="block text-sm font-medium text-slate-700">Dispute identifier
                        <input name="dispute_id" type="text" placeholder="2867"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Refund amount
                        <input name="refund_amount" type="number" min="0" step="1" placeholder="1002"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Uploaded filename
                        <input name="uploaded_filename" type="text" placeholder="receipt.pdf"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Update dispute
                </button>
            </form>

            <form method="post" action="/paystack/demo/disputes" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="evidence">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Add evidence</p>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Dispute identifier
                        <input name="dispute_id" type="text" placeholder="2867"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Customer email
                        <input name="customer_email" type="email" value="customer@example.com"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Customer name
                        <input name="customer_name" type="text" value="Jane Doe"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Customer phone
                        <input name="customer_phone" type="text" value="08023456789"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Service details
                        <input name="service_details" type="text" value="Service details"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Delivery address
                        <input name="delivery_address" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Delivery date
                        <input name="delivery_date" type="text" placeholder="2026-01-01"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Add evidence
                </button>
            </form>

            <form method="post" action="/paystack/demo/disputes" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="upload-url">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Get upload URL</p>
                <div class="grid gap-3">
                    <label class="block text-sm font-medium text-slate-700">Dispute identifier
                        <input name="dispute_id" type="text" placeholder="2867"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Upload filename
                        <input name="upload_filename" type="text" placeholder="receipt.pdf"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Get upload URL
                </button>
            </form>

            <form method="post" action="/paystack/demo/disputes" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="resolve">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Resolve dispute</p>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Dispute identifier
                        <input name="dispute_id" type="text" placeholder="2867"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Resolution
                        <select name="resolution"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none">
                            <option value="merchant-accepted">merchant-accepted</option>
                            <option value="declined">declined</option>
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Message
                        <input name="message" type="text" placeholder="Merchant accepted"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Refund amount
                        <input name="refund_amount" type="number" min="0" step="1" placeholder="1002"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Uploaded filename
                        <input name="uploaded_filename" type="text" placeholder="receipt.pdf"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Evidence id
                        <input name="evidence" type="number" min="1" step="1" placeholder="21"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button class="mt-4 w-full bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">
                    Resolve dispute
                </button>
            </form>

            <form method="post" action="/paystack/demo/disputes" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="export">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Export disputes</p>
                <div class="grid gap-3 md:grid-cols-2">
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
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Transaction filter
                        <input name="transaction" type="text" placeholder="5991760"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Status
                        <select name="status"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none">
                            <option value="">Any status</option>
                            <option value="awaiting-merchant-feedback">awaiting-merchant-feedback</option>
                            <option value="awaiting-bank-feedback">awaiting-bank-feedback</option>
                            <option value="pending">pending</option>
                            <option value="resolved">resolved</option>
                        </select>
                    </label>
                </div>
                <button class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Export disputes
                </button>
            </form>
        </div>
    </section>
</x-paystack-demo.layout>
