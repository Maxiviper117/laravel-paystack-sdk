<?php

namespace Maxiviper117\Paystack\Data\Output\Subscription;

use Maxiviper117\Paystack\Data\Shared\PaystackResponseData;
use Maxiviper117\Paystack\Support\Payload;
use Override;

class GenerateSubscriptionUpdateLinkResponseData extends PaystackResponseData
{
    public function __construct(
        public bool $status,
        public string $message,
        public string $link,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    #[Override]
    public static function fromPayload(array $payload): self
    {
        $data = Payload::nullableArray($payload, 'data') ?? [];
        /** @var array<string, mixed> $linkPayload */
        $linkPayload = [];

        foreach ($data as $key => $value) {
            if (\is_string($key)) {
                $linkPayload[$key] = $value;
            }
        }

        return new self(
            status: Payload::bool($payload, 'status'),
            message: Payload::string($payload, 'message'),
            link: Payload::string($linkPayload, 'link'),
        );
    }
}
