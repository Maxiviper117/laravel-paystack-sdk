<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\ChargeDisputePayloadData;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class ChargeDisputeWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public int|string|null $disputeId,
        public string $status,
        public ?int $refundAmount,
        public ?string $currency,
        public ?string $domain,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $dueAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $resolvedAt,
        public ?string $transactionReference,
        public ChargeDisputePayloadData $dispute,
        public array $rawData = [],
    ) {}
}
