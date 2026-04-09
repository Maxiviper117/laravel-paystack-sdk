<?php

namespace Maxiviper117\Paystack\Data\Input\Customer;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class SetCustomerRiskActionInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $customer,
        public CustomerRiskAction|string|null $riskAction = null,
        public array $extra = [],
    ) {
        if (trim($this->customer) === '') {
            throw new InvalidPaystackInputException('The Paystack customer identifier cannot be empty.');
        }

        if ($this->riskAction !== null && ! (
            $this->riskAction instanceof CustomerRiskAction
            || in_array($this->riskAction, CustomerRiskAction::values(), true)
        )) {
            throw new InvalidPaystackInputException('The Paystack customer risk action must be allow, deny, or default.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;
        $payload['customer'] = $this->customer;

        if ($this->riskAction !== null) {
            $payload['risk_action'] = $this->riskAction instanceof CustomerRiskAction ? $this->riskAction->value : $this->riskAction;
        }

        return $payload;
    }
}
