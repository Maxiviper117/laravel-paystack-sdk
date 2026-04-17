<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Plan;

use InvalidArgumentException;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Support\Amount;
use Spatie\LaravelData\Data;

class CreatePlanInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $name,
        public int|float|string $amount,
        public string $interval,
        public ?string $description = null,
        public ?string $currency = null,
        public ?int $invoiceLimit = null,
        public ?bool $sendInvoices = null,
        public ?bool $sendSms = null,
        public array $extra = [],
    ) {
        if (trim($this->name) === '') {
            throw new InvalidPaystackInputException('The Paystack plan name cannot be empty.');
        }

        if (trim($this->interval) === '') {
            throw new InvalidPaystackInputException('The Paystack plan interval cannot be empty.');
        }

        if ($this->invoiceLimit !== null && $this->invoiceLimit < 0) {
            throw new InvalidPaystackInputException('The Paystack invoice limit cannot be negative.');
        }

        try {
            Amount::toSubunit($this->amount);
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new InvalidPaystackInputException($invalidArgumentException->getMessage(), 0, $invalidArgumentException);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;
        $payload['name'] = $this->name;
        $payload['amount'] = (string) Amount::toSubunit($this->amount);
        $payload['interval'] = $this->interval;

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        if ($this->currency !== null) {
            $payload['currency'] = $this->currency;
        }

        if ($this->invoiceLimit !== null) {
            $payload['invoice_limit'] = $this->invoiceLimit;
        }

        if ($this->sendInvoices !== null) {
            $payload['send_invoices'] = $this->sendInvoices;
        }

        if ($this->sendSms !== null) {
            $payload['send_sms'] = $this->sendSms;
        }

        return $payload;
    }
}
