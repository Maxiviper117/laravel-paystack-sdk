<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeRemindedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeResolvedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\ChargeDisputePayloadData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;

class ChargeDisputeWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): ChargeDisputeWebhookData
    {
        $payload = self::requireObjectData($event);
        $dispute = ChargeDisputePayloadData::fromPayload($payload);

        if ($dispute->status === null || trim($dispute->status) === '') {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing a non-empty [status] field.',
                $event->event,
            ));
        }

        $data = [
            'event' => $event->event,
            'disputeId' => $dispute->id,
            'status' => $dispute->status,
            'refundAmount' => $dispute->refundAmount,
            'currency' => $dispute->currency,
            'domain' => $dispute->domain,
            'dueAt' => $dispute->dueAt,
            'resolvedAt' => $dispute->resolvedAt,
            'transactionReference' => $dispute->transactionReference,
            'dispute' => $dispute,
            'rawData' => $payload,
        ];

        return match ($event->event) {
            'charge.dispute.create' => new ChargeDisputeCreatedWebhookData(...$data),
            'charge.dispute.remind' => new ChargeDisputeRemindedWebhookData(...$data),
            'charge.dispute.resolve' => new ChargeDisputeResolvedWebhookData(...$data),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported dispute webhook event [%s] requested for typed mapping.',
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
