<?php

namespace Maxiviper117\Paystack\Data\Input\Plan;

use InvalidArgumentException;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Support\Amount;
use Spatie\LaravelData\Data;

class UpdatePlanInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $planCode,
        public ?string $name = null,
        public int|float|string|null $amount = null,
        public ?string $interval = null,
        public ?string $description = null,
        public ?string $currency = null,
        public ?int $invoiceLimit = null,
        public ?bool $sendInvoices = null,
        public ?bool $sendSms = null,
        public array $extra = [],
    ) {
        if (trim($this->planCode) === '') {
            throw new InvalidPaystackInputException('The Paystack plan code cannot be empty.');
        }

        if ($this->name !== null && trim($this->name) === '') {
            throw new InvalidPaystackInputException('The Paystack plan name cannot be empty.');
        }

        if ($this->interval !== null && trim($this->interval) === '') {
            throw new InvalidPaystackInputException('The Paystack plan interval cannot be empty.');
        }

        if ($this->invoiceLimit !== null && $this->invoiceLimit < 0) {
            throw new InvalidPaystackInputException('The Paystack invoice limit cannot be negative.');
        }

        if ($this->amount !== null) {
            try {
                Amount::toSubunit($this->amount);
            } catch (InvalidArgumentException $exception) {
                throw new InvalidPaystackInputException($exception->getMessage(), 0, $exception);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;

        if ($this->name !== null) {
            $payload['name'] = $this->name;
        }

        if ($this->amount !== null) {
            $payload['amount'] = (string) Amount::toSubunit($this->amount);
        }

        if ($this->interval !== null) {
            $payload['interval'] = $this->interval;
        }

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
