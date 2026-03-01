<?php

namespace Maxiviper117\Paystack\Actions\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\CreateSubscriptionResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\CreateSubscriptionRequest;

final class CreateSubscriptionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(CreateSubscriptionInputData $input): CreateSubscriptionResponseData
    {
        $response = $this->connector->send(new CreateSubscriptionRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof CreateSubscriptionResponseData);

        return $dto;
    }

    public function __invoke(CreateSubscriptionInputData $input): CreateSubscriptionResponseData
    {
        return $this->execute($input);
    }
}
