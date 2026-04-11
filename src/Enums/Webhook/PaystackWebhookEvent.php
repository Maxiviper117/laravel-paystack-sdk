<?php

namespace Maxiviper117\Paystack\Enums\Webhook;

enum PaystackWebhookEvent: string
{
    case ChargeSuccess = 'charge.success';
    case ChargeDisputeCreate = 'charge.dispute.create';
    case ChargeDisputeRemind = 'charge.dispute.remind';
    case ChargeDisputeResolve = 'charge.dispute.resolve';
    case CustomerIdentificationSuccess = 'customeridentification.success';
    case CustomerIdentificationFailed = 'customeridentification.failed';
    case DedicatedAccountAssignSuccess = 'dedicatedaccount.assign.success';
    case DedicatedAccountAssignFailed = 'dedicatedaccount.assign.failed';
    case InvoiceCreate = 'invoice.create';
    case InvoiceUpdate = 'invoice.update';
    case InvoicePaymentFailed = 'invoice.payment_failed';
    case PaymentRequestPending = 'paymentrequest.pending';
    case PaymentRequestSuccess = 'paymentrequest.success';
    case RefundPending = 'refund.pending';
    case RefundProcessing = 'refund.processing';
    case RefundProcessed = 'refund.processed';
    case RefundFailed = 'refund.failed';
    case SubscriptionCreate = 'subscription.create';
    case SubscriptionNotRenew = 'subscription.not_renew';
    case SubscriptionDisable = 'subscription.disable';
    case SubscriptionExpiringCards = 'subscription.expiring_cards';
    case TransferSuccess = 'transfer.success';
    case TransferFailed = 'transfer.failed';
    case TransferReversed = 'transfer.reversed';
}
