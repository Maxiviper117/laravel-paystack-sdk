<?php

namespace Maxiviper117\Paystack\Concerns;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Output\Customer\CreateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\CreateSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\DisableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\EnableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\FetchSubscriptionResponseData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Models\PaystackCustomer;
use Maxiviper117\Paystack\Models\PaystackSubscription;
use Maxiviper117\Paystack\PaystackManager;

/**
 * @mixin Model
 */
trait Billable
{
    /**
     * @return MorphOne<PaystackCustomer, $this>
     */
    public function paystackCustomer(): MorphOne
    {
        return $this->morphOne(PaystackCustomer::class, 'billable');
    }

    /**
     * @return MorphMany<PaystackSubscription, $this>
     */
    public function paystackSubscriptions(): MorphMany
    {
        return $this->morphMany(PaystackSubscription::class, 'billable');
    }

    public function hasPaystackCustomer(): bool
    {
        return $this->paystackCustomer()->exists();
    }

    public function hasPaystackSubscription(string $name = 'default'): bool
    {
        return $this->paystackSubscriptions()->where('name', $name)->exists();
    }

    public function paystackCustomerCode(): ?string
    {
        return $this->storedPaystackCustomer()?->customer_code;
    }

    public function paystackSubscription(string $name = 'default'): ?PaystackSubscription
    {
        /** @var PaystackSubscription|null $subscription */
        $subscription = $this->paystackSubscriptions()
            ->where('name', $name)
            ->first();

        return $subscription;
    }

    public function createAsPaystackCustomer(?CreateCustomerInputData $input = null): CreateCustomerResponseData
    {
        $response = $this->paystackManager()->createCustomer($input ?? $this->newPaystackCustomerInput());

        PaystackCustomer::syncForBillable($this, $response->customer);

        return $response;
    }

    public function updateAsPaystackCustomer(?UpdateCustomerInputData $input = null): UpdateCustomerResponseData
    {
        $customerCode = $this->paystackCustomerCode();

        if ($customerCode === null) {
            throw new InvalidPaystackInputException('The billable model does not have a stored Paystack customer code.');
        }

        $response = $this->paystackManager()->updateCustomer($input ?? $this->newPaystackCustomerUpdateInput($customerCode));

        PaystackCustomer::syncForBillable($this, $response->customer);

        return $response;
    }

    public function syncAsPaystackCustomer(): PaystackCustomer
    {
        if ($this->hasPaystackCustomer()) {
            $this->updateAsPaystackCustomer();
        } else {
            $this->createAsPaystackCustomer();
        }

        /** @var PaystackCustomer */
        return $this->paystackCustomer()->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function createPaystackSubscription(
        string $planCode,
        string $name = 'default',
        ?string $authorization = null,
        ?string $startDate = null,
        array $extra = [],
    ): CreateSubscriptionResponseData {
        $customer = $this->syncAsPaystackCustomer();
        $customerCode = $customer->customer_code;

        if ($customerCode === null || trim($customerCode) === '') {
            throw new InvalidPaystackInputException('The billable model does not have a usable Paystack customer code.');
        }

        $response = $this->paystackManager()->createSubscription(new CreateSubscriptionInputData(
            customer: $customerCode,
            plan: $planCode,
            authorization: $authorization,
            startDate: $startDate,
            extra: $extra,
        ));

        PaystackSubscription::syncForBillable($this, $response->subscription, $name, $customer);

        return $response;
    }

    public function fetchPaystackSubscription(string|int $idOrCode, string $name = 'default'): FetchSubscriptionResponseData
    {
        $response = $this->paystackManager()->fetchSubscription(new FetchSubscriptionInputData($idOrCode));

        PaystackSubscription::syncForBillable($this, $response->subscription, $name, $this->storedPaystackCustomer());

        return $response;
    }

    public function enablePaystackSubscription(string $name = 'default'): EnableSubscriptionResponseData
    {
        $subscription = $this->requirePaystackSubscription($name);

        return $this->paystackManager()->enableSubscription(new EnableSubscriptionInputData(
            code: $subscription->subscription_code,
            token: $subscription->email_token ?? $this->requireSubscriptionEmailToken($subscription),
        ));
    }

    public function disablePaystackSubscription(string $name = 'default'): DisableSubscriptionResponseData
    {
        $subscription = $this->requirePaystackSubscription($name);

        return $this->paystackManager()->disableSubscription(new DisableSubscriptionInputData(
            code: $subscription->subscription_code,
            token: $subscription->email_token ?? $this->requireSubscriptionEmailToken($subscription),
        ));
    }

    protected function newPaystackCustomerInput(): CreateCustomerInputData
    {
        return new CreateCustomerInputData(
            email: $this->paystackBillableEmail(),
            firstName: $this->paystackBillableFirstName(),
            lastName: $this->paystackBillableLastName(),
            phone: $this->paystackBillablePhone(),
            metadata: $this->paystackBillableMetadata(),
        );
    }

    protected function newPaystackCustomerUpdateInput(string $customerCode): UpdateCustomerInputData
    {
        return new UpdateCustomerInputData(
            customerCode: $customerCode,
            email: $this->paystackBillableEmail(),
            firstName: $this->paystackBillableFirstName(),
            lastName: $this->paystackBillableLastName(),
            phone: $this->paystackBillablePhone(),
            metadata: $this->paystackBillableMetadata(),
        );
    }

    protected function paystackBillableEmail(): string
    {
        $email = $this->getAttribute('email');

        if (! is_string($email) || trim($email) === '') {
            throw new InvalidPaystackInputException('The billable model must expose a non-empty email attribute or override paystackBillableEmail().');
        }

        return $email;
    }

    protected function paystackBillableFirstName(): ?string
    {
        $firstName = $this->getAttribute('first_name');

        return is_string($firstName) && trim($firstName) !== '' ? $firstName : null;
    }

    protected function paystackBillableLastName(): ?string
    {
        $lastName = $this->getAttribute('last_name');

        return is_string($lastName) && trim($lastName) !== '' ? $lastName : null;
    }

    protected function paystackBillablePhone(): ?string
    {
        $phone = $this->getAttribute('phone');

        return is_string($phone) && trim($phone) !== '' ? $phone : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function paystackBillableMetadata(): array
    {
        return [
            'billable_type' => $this->getMorphClass(),
            'billable_id' => $this->getKey(),
        ];
    }

    protected function paystackManager(): PaystackManager
    {
        $container = Container::getInstance();

        /** @var PaystackManager */
        return $container->make(PaystackManager::class);
    }

    protected function storedPaystackCustomer(): ?PaystackCustomer
    {
        /** @var PaystackCustomer|null $customer */
        $customer = $this->paystackCustomer()->first();

        return $customer;
    }

    protected function requirePaystackSubscription(string $name): PaystackSubscription
    {
        $subscription = $this->paystackSubscription($name);

        if ($subscription === null) {
            throw new InvalidPaystackInputException(sprintf(
                'No stored Paystack subscription named [%s] exists for this billable model.',
                $name
            ));
        }

        return $subscription;
    }

    protected function requireSubscriptionEmailToken(PaystackSubscription $subscription): string
    {
        throw new InvalidPaystackInputException(sprintf(
            'The stored Paystack subscription [%s] does not have an email token. Fetch or recreate the subscription before enabling or disabling it.',
            $subscription->subscription_code
        ));
    }
}
