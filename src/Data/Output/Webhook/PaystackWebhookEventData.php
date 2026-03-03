<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook;

use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaystackTypedWebhookData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhook;
use Maxiviper117\Paystack\Support\Webhooks\PaystackTypedWebhookDataResolver;
use Spatie\LaravelData\Data;

class PaystackWebhookEventData extends Data
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $event,
        public string $resourceType,
        public array $data,
        public array $payload,
        public ?string $occurredAt = null,
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

        if ($data === null || array_is_list($data)) {
            throw new MalformedWebhookPayloadException('The Paystack webhook payload is missing an object data payload.');
        }

        /** @var array<string, mixed> $data */
        return new self(
            event: $event,
            resourceType: PaystackWebhook::inferResourceType($event),
            data: $data,
            payload: $payload,
            occurredAt: Payload::nullableString($data, 'paid_at')
                ?? Payload::nullableString($data, 'created_at')
                ?? Payload::nullableString($payload, 'created_at'),
            domain: Payload::nullableString($data, 'domain'),
            id: Payload::intOrStringOrNull($data, 'id'),
        );
    }

    public function is(string $event): bool
    {
        return $this->event === $event;
    }

    public function supportsTypedData(): bool
    {
        return PaystackTypedWebhookDataResolver::supports($this->event);
    }

    public function typedData(): ?PaystackTypedWebhookData
    {
        return PaystackTypedWebhookDataResolver::resolve($this);
    }
}
