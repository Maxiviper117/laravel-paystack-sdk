<x-paystack-demo.layout
    title="Webhooks Demo"
    heading="Webhooks"
    description="Inspect the webhook endpoint, latest stored call, and the latest cached parsed event."
    :pages="$pages"
    :latest-webhook-call="$latestWebhookCall ?? null"
    :latest-webhook-event="$latestWebhookEvent ?? null"
    current-path="/paystack/demo/webhooks"
>
    <section class="rounded-3xl border border-slate-200 bg-white p-6">
        <h2 class="text-2xl font-bold text-slate-900">Webhooks</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Inspect the webhook endpoint, latest stored call, and the latest cached parsed event.</p>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                <p class="font-semibold text-slate-900">POST endpoint</p>
                <p class="mt-1 break-all">{{ url('/paystack/test/webhook') }}</p>
                <p class="mt-3 font-semibold text-slate-900">Latest event endpoint</p>
                <p class="mt-1 break-all">{{ url('/paystack/test/webhook/latest-event') }}</p>
                <p class="mt-3 font-semibold text-slate-900">Latest call endpoint</p>
                <p class="mt-1 break-all">{{ url('/paystack/test/webhook/latest-call') }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Latest cached event</p>
                <pre class="mt-3 overflow-auto text-xs leading-6 text-slate-800">{{ json_encode($latestWebhookEvent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Latest stored webhook call</p>
                <pre class="mt-3 overflow-auto text-xs leading-6 text-slate-800">{{ json_encode($latestWebhookCall?->only(['id', 'name', 'url', 'headers', 'payload', 'exception', 'created_at']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Webhook notes</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Post a signed Paystack payload to the endpoint above, then inspect the stored call and the cached event payload here.
                </p>
            </div>
        </div>
    </section>
</x-paystack-demo.layout>
