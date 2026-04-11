<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\DedicatedAccountAssignFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\DedicatedAccountAssignSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\DedicatedAccountAssignWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\DedicatedAccountData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;

class DedicatedAccountWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): DedicatedAccountAssignWebhookData
    {
        $payload = self::requireObjectData($event);
        $customerPayload = Payload::nullableArray($payload, 'customer');
        $identificationPayload = Payload::nullableArray($payload, 'identification');
        $dedicatedAccountPayload = Payload::nullableArray($payload, 'dedicated_account');

        if ($customerPayload === null || array_is_list($customerPayload)) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing an object [customer] field.',
                $event->event,
            ));
        }

        if ($identificationPayload === null || array_is_list($identificationPayload)) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing an object [identification] field.',
                $event->event,
            ));
        }

        /** @var array<string, mixed> $customerPayload */
        /** @var array<string, mixed> $identificationPayload */
        $dedicatedAccount = null;

        if ($dedicatedAccountPayload !== null) {
            /** @var array<string, mixed> $dedicatedAccountPayload */
            $dedicatedAccount = DedicatedAccountData::fromPayload($dedicatedAccountPayload);
        }

        $data = [
            'event' => $event->event,
            'status' => Payload::string($identificationPayload, 'status'),
            'customer' => WebhookCustomerPayloadData::fromPayload($customerPayload),
            'dedicatedAccount' => $dedicatedAccount,
            'rawData' => $payload,
        ];

        return match ($event->event) {
            'dedicatedaccount.assign.success' => new DedicatedAccountAssignSuccessWebhookData(...$data),
            'dedicatedaccount.assign.failed' => new DedicatedAccountAssignFailedWebhookData(...$data),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported dedicated account webhook event [%s] requested for typed mapping.',
                $event->event,
            )),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function requireObjectData(PaystackWebhookEventData $event): array
    {
        $data = $event->data;

        if (array_is_list($data)) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing an object data payload.',
                $event->event,
            ));
        }

        /** @var array<string, mixed> $data */
        return $data;
    }
}
