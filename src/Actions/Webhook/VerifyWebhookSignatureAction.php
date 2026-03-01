<?php

namespace Maxiviper117\Paystack\Actions\Webhook;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Maxiviper117\Paystack\Data\Input\Webhook\VerifyWebhookSignatureInputData;
use Maxiviper117\Paystack\Data\Output\Webhook\VerifyWebhookSignatureResponseData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Exceptions\PaystackConfigurationException;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhook;

final class VerifyWebhookSignatureAction
{
    public function __construct(
        protected ConfigRepository $config
    ) {}

    public function execute(VerifyWebhookSignatureInputData $input): VerifyWebhookSignatureResponseData
    {
        $rawConfig = $this->config->get('paystack', []);

        if (! is_array($rawConfig)) {
            $rawConfig = [];
        }

        /** @var array<string, mixed> $paystackConfig */
        $paystackConfig = $rawConfig;
        $secretKey = Payload::string($paystackConfig, 'secret_key');

        if ($secretKey === '') {
            throw new PaystackConfigurationException('The Paystack secret key is not configured.');
        }

        PaystackWebhook::verifySignature($input->payload, $secretKey, $input->signature);

        $payload = PaystackWebhook::decodePayload($input->payload);
        $event = Payload::string($payload, 'event');
        $data = Payload::nullableArray($payload, 'data');

        if ($event === '') {
            throw new MalformedWebhookPayloadException('The Paystack webhook payload is missing an event name.');
        }

        if ($data === null || array_is_list($data)) {
            throw new MalformedWebhookPayloadException('The Paystack webhook payload is missing an object data payload.');
        }

        /** @var array<string, mixed> $data */
        return VerifyWebhookSignatureResponseData::fromPayload(
            payload: $payload,
            data: $data,
            resourceType: PaystackWebhook::inferResourceType($event),
        );
    }

    public function __invoke(VerifyWebhookSignatureInputData $input): VerifyWebhookSignatureResponseData
    {
        return $this->execute($input);
    }
}
