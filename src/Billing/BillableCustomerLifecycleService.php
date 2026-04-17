<?php

namespace Maxiviper117\Paystack\Billing;

use Illuminate\Database\Eloquent\Model;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\CreateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Models\PaystackCustomer;
use Maxiviper117\Paystack\PaystackManager;

class BillableCustomerLifecycleService
{
    public function __construct(
        protected PaystackManager $paystack
    ) {}

    public function create(Model $billable, ?CreateCustomerInputData $input = null): CreateCustomerResponseData
    {
        $response = $this->paystack->createCustomer($input ?? $this->newCustomerInput($billable));

        PaystackCustomer::syncForBillable($billable, $response->customer);

        return $response;
    }

    public function update(Model $billable, ?UpdateCustomerInputData $input = null): UpdateCustomerResponseData
    {
        $customerCode = $this->customerCode($billable);

        if ($customerCode === null) {
            throw new InvalidPaystackInputException('The billable model does not have a stored Paystack customer code.');
        }

        $response = $this->paystack->updateCustomer($input ?? $this->newCustomerUpdateInput($billable, $customerCode));

        PaystackCustomer::syncForBillable($billable, $response->customer);

        return $response;
    }

    public function sync(Model $billable): PaystackCustomer
    {
        if ($this->hasCustomer($billable)) {
            $this->update($billable);
        } else {
            $this->create($billable);
        }

        /** @var PaystackCustomer */
        return PaystackCustomer::query()
            ->where('billable_type', $billable->getMorphClass())
            ->where('billable_id', $billable->getKey())
            ->firstOrFail();
    }

    private function hasCustomer(Model $billable): bool
    {
        return PaystackCustomer::query()
            ->where('billable_type', $billable->getMorphClass())
            ->where('billable_id', $billable->getKey())
            ->exists();
    }

    private function customerCode(Model $billable): ?string
    {
        /** @var PaystackCustomer|null $customer */
        $customer = PaystackCustomer::query()
            ->where('billable_type', $billable->getMorphClass())
            ->where('billable_id', $billable->getKey())
            ->first();

        return $customer?->customer_code;
    }

    private function newCustomerInput(Model $billable): CreateCustomerInputData
    {
        return new CreateCustomerInputData(
            email: $this->billableEmail($billable),
            firstName: $this->billableFirstName($billable),
            lastName: $this->billableLastName($billable),
            phone: $this->billablePhone($billable),
            metadata: $this->billableMetadata($billable),
        );
    }

    private function newCustomerUpdateInput(Model $billable, string $customerCode): UpdateCustomerInputData
    {
        return new UpdateCustomerInputData(
            customerCode: $customerCode,
            email: $this->billableEmail($billable),
            firstName: $this->billableFirstName($billable),
            lastName: $this->billableLastName($billable),
            phone: $this->billablePhone($billable),
            metadata: $this->billableMetadata($billable),
        );
    }

    private function billableEmail(Model $billable): string
    {
        $email = $billable->getAttribute('email');

        if (! is_string($email) || trim($email) === '') {
            throw new InvalidPaystackInputException('The billable model must expose a non-empty email attribute or override paystackBillableEmail().');
        }

        return $email;
    }

    private function billableFirstName(Model $billable): ?string
    {
        $firstName = $billable->getAttribute('first_name');

        return is_string($firstName) && trim($firstName) !== '' ? $firstName : null;
    }

    private function billableLastName(Model $billable): ?string
    {
        $lastName = $billable->getAttribute('last_name');

        return is_string($lastName) && trim($lastName) !== '' ? $lastName : null;
    }

    private function billablePhone(Model $billable): ?string
    {
        $phone = $billable->getAttribute('phone');

        return is_string($phone) && trim($phone) !== '' ? $phone : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function billableMetadata(Model $billable): array
    {
        return [
            'billable_type' => $billable->getMorphClass(),
            'billable_id' => $billable->getKey(),
        ];
    }
}
