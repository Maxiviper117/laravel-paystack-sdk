<?php

namespace Maxiviper117\Paystack\Billing;

use Illuminate\Database\Eloquent\Model;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\CreateSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\DisableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\EnableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\FetchSubscriptionResponseData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Models\PaystackCustomer;
use Maxiviper117\Paystack\Models\PaystackSubscription;
use Maxiviper117\Paystack\PaystackManager;

class BillableSubscriptionLifecycleService
{
    public function __construct(
        protected PaystackManager $paystack,
        protected BillableCustomerLifecycleService $customers,
    ) {}

    /**
     * @param  array<string, mixed>  $extra
     */
    public function create(
        Model $billable,
        string $planCode,
        string $name = 'default',
        ?string $authorization = null,
        ?string $startDate = null,
        array $extra = [],
    ): CreateSubscriptionResponseData {
        $customer = $this->customers->sync($billable);
        $customerCode = $customer->customer_code;

        if ($customerCode === null || trim($customerCode) === '') {
            throw new InvalidPaystackInputException('The billable model does not have a usable Paystack customer code.');
        }

        $response = $this->paystack->createSubscription(new CreateSubscriptionInputData(
            customer: $customerCode,
            plan: $planCode,
            authorization: $authorization,
            startDate: $startDate,
            extra: $extra,
        ));

        PaystackSubscription::syncForBillable($billable, $response->subscription, $name, $customer);

        return $response;
    }

    public function fetch(Model $billable, string|int $idOrCode, string $name = 'default'): FetchSubscriptionResponseData
    {
        $response = $this->paystack->fetchSubscription(new FetchSubscriptionInputData($idOrCode));

        PaystackSubscription::syncForBillable($billable, $response->subscription, $name, $this->storedCustomer($billable));

        return $response;
    }

    public function enable(Model $billable, string $name = 'default'): EnableSubscriptionResponseData
    {
        $subscription = $this->requireSubscription($billable, $name);

        return $this->paystack->enableSubscription(new EnableSubscriptionInputData(
            code: $subscription->subscription_code,
            token: $subscription->email_token ?? $this->requireSubscriptionEmailToken($subscription),
        ));
    }

    public function disable(Model $billable, string $name = 'default'): DisableSubscriptionResponseData
    {
        $subscription = $this->requireSubscription($billable, $name);

        return $this->paystack->disableSubscription(new DisableSubscriptionInputData(
            code: $subscription->subscription_code,
            token: $subscription->email_token ?? $this->requireSubscriptionEmailToken($subscription),
        ));
    }

    private function storedCustomer(Model $billable): ?PaystackCustomer
    {
        /** @var PaystackCustomer|null $customer */
        $customer = PaystackCustomer::query()
            ->where('billable_type', $billable->getMorphClass())
            ->where('billable_id', $billable->getKey())
            ->first();

        return $customer;
    }

    private function requireSubscription(Model $billable, string $name): PaystackSubscription
    {
        /** @var PaystackSubscription|null $subscription */
        $subscription = PaystackSubscription::query()
            ->where('billable_type', $billable->getMorphClass())
            ->where('billable_id', $billable->getKey())
            ->where('name', $name)
            ->first();

        if ($subscription === null) {
            throw new InvalidPaystackInputException(sprintf(
                'No stored Paystack subscription named [%s] exists for this billable model.',
                $name
            ));
        }

        return $subscription;
    }

    private function requireSubscriptionEmailToken(PaystackSubscription $subscription): string
    {
        throw new InvalidPaystackInputException(sprintf(
            'The stored Paystack subscription [%s] does not have an email token. Fetch or recreate the subscription before enabling or disabling it.',
            $subscription->subscription_code
        ));
    }
}
