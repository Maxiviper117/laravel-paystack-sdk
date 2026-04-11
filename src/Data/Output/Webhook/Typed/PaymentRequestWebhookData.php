<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class PaymentRequestWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<mixed>|null  $metadata
     * @param  array<mixed>  $lineItems
     * @param  array<mixed>  $tax
     * @param  array<mixed>  $notifications
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public int|string|null $id,
        public string $requestCode,
        public string $status,
        public int $amount,
        public bool $paid,
        public ?string $currency,
        public ?string $domain,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $dueDate,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $paidAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        public ?string $description,
        public ?string $invoiceNumber,
        public ?string $offlineReference,
        public int|string|null $customerId,
        public ?bool $hasInvoice,
        public ?string $pdfUrl,
        public ?array $metadata,
        public array $lineItems = [],
        public array $tax = [],
        public array $notifications = [],
        public array $rawData = [],
    ) {}
}
