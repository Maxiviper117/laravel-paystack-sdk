<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaystackTypedWebhookData;
use Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Maxiviper117\Paystack\Support\Webhooks\PaystackTypedWebhookDataResolver;
use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhook;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class PaystackWebhookEventData extends Data
{
    /**
     * @param  array<mixed>  $data
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $event,
        public string $resourceType,
        public array $data,
        public array $payload,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $occurredAt = null,
        public ?string $domain = null,
        public int|string|null $id = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $event = Payload::string($payload, 'event');
        $data = Payload::nullableArray($payload, 'data');

        if ($event === '') {
            throw new MalformedWebhookPayloadException('The Paystack webhook payload is missing an event name.');
        }

        if ($data === null) {
            throw new MalformedWebhookPayloadException('The Paystack webhook payload is missing a data payload.');
        }

        $dataObject = self::objectDataOrNull($data);

        return new self(
            event: $event,
            resourceType: PaystackWebhook::inferResourceType($event),
            data: $data,
            payload: $payload,
            occurredAt: PaystackDate::nullable(
                ($dataObject === null ? null : Payload::nullableString($dataObject, 'paid_at'))
                ?? ($dataObject === null ? null : Payload::nullableString($dataObject, 'created_at'))
                ?? Payload::nullableString($payload, 'created_at')
            ),
            domain: $dataObject === null ? null : Payload::nullableString($dataObject, 'domain'),
            id: $dataObject === null ? null : Payload::intOrStringOrNull($dataObject, 'id'),
        );
    }

    public function is(string|PaystackWebhookEvent $event): bool
    {
        return $this->event === ($event instanceof PaystackWebhookEvent ? $event->value : $event);
    }

    public function supportsTypedData(): bool
    {
        return PaystackTypedWebhookDataResolver::supports($this->event);
    }

    public function typedData(): ?PaystackTypedWebhookData
    {
        return PaystackTypedWebhookDataResolver::resolve($this);
    }

    /**
     * @param  array<mixed>  $data
     * @return array<string, mixed>|null
     */
    private static function objectDataOrNull(array $data): ?array
    {
        if (array_is_list($data)) {
            return null;
        }

        /** @var array<string, mixed> $data */
        return $data;
    }
}
