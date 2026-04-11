<?php

namespace Maxiviper117\Paystack\Data\Input\Dispute;

use DateTimeImmutable;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class AddDisputeEvidenceInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public int|string $id,
        public string $customerEmail,
        public string $customerName,
        public string $customerPhone,
        public string $serviceDetails,
        public ?string $deliveryAddress = null,
        public ?string $deliveryDate = null,
        public array $extra = [],
    ) {
        if (\is_int($this->id) && $this->id < 1) {
            throw new InvalidPaystackInputException('The Paystack dispute identifier must be greater than zero.');
        }

        if (\is_string($this->id) && trim($this->id) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute identifier cannot be empty.');
        }

        foreach (
            [
                'customerEmail' => $this->customerEmail,
                'customerName' => $this->customerName,
                'customerPhone' => $this->customerPhone,
                'serviceDetails' => $this->serviceDetails,
            ] as $label => $value
        ) {
            if (trim($value) === '') {
                throw new InvalidPaystackInputException(sprintf('The Paystack dispute %s cannot be empty.', $label));
            }
        }

        if ($this->deliveryAddress !== null && trim($this->deliveryAddress) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute delivery address cannot be empty.');
        }

        if ($this->deliveryDate !== null) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $this->deliveryDate);

            if ($date === false || $date->format('Y-m-d') !== $this->deliveryDate) {
                throw new InvalidPaystackInputException('The Paystack dispute delivery date must use the YYYY-MM-DD format.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;

        $payload['customer_email'] = $this->customerEmail;
        $payload['customer_name'] = $this->customerName;
        $payload['customer_phone'] = $this->customerPhone;
        $payload['service_details'] = $this->serviceDetails;

        if ($this->deliveryAddress !== null) {
            $payload['delivery_address'] = $this->deliveryAddress;
        }

        if ($this->deliveryDate !== null) {
            $payload['delivery_date'] = $this->deliveryDate;
        }

        return $payload;
    }
}
