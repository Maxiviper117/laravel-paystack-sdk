<?php

namespace Maxiviper117\Paystack\Actions\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\DisableSubscriptionResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\DisableSubscriptionRequest;

/**
 * Disable a subscription using its code and email token.
 *
 * @see https://paystack.com/docs/api/subscription/#disable-subscription
 */
final class DisableSubscriptionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(DisableSubscriptionInputData $input): DisableSubscriptionResponseData
    {
        $response = $this->connector->send(new DisableSubscriptionRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof DisableSubscriptionResponseData);

        return $dto;
    }

    public function __invoke(DisableSubscriptionInputData $input): DisableSubscriptionResponseData
    {
        return $this->execute($input);
    }
}
