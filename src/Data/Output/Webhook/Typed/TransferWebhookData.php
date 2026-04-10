<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\TransferIntegrationPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\TransferRecipientPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\TransferSessionPayloadData;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class TransferWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<mixed>|null  $failures
     * @param  array<mixed>|null  $sourceDetails
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public int|string|null $id,
        public string $reference,
        public string $status,
        public ?string $transferCode,
        public int $amount,
        public ?string $currency,
        public ?string $domain,
        public ?string $reason,
        public ?string $source,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $transferredAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $updatedAt,
        public ?string $titanCode,
        public ?array $failures,
        public ?array $sourceDetails,
        public ?TransferIntegrationPayloadData $integration,
        public ?TransferRecipientPayloadData $recipient,
        public ?TransferSessionPayloadData $session,
        public ?int $feeCharged,
        public ?string $gatewayResponse,
        public array $rawData = [],
    ) {}
}
