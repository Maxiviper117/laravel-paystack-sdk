<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\EnableSubscriptionResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\EnableSubscriptionRequest;

/**
 * Enable a subscription using its code and email token.
 *
 * @see https://paystack.com/docs/api/subscription/#enable-subscription
 */
final class EnableSubscriptionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(EnableSubscriptionInputData $input): EnableSubscriptionResponseData
    {
        $response = $this->connector->send(new EnableSubscriptionRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof EnableSubscriptionResponseData);

        return $dto;
    }

    public function __invoke(EnableSubscriptionInputData $input): EnableSubscriptionResponseData
    {
        return $this->execute($input);
    }
}
