<?php

namespace Maxiviper117\Paystack\Actions\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\FetchSubscriptionResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\FetchSubscriptionRequest;

final class FetchSubscriptionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(FetchSubscriptionInputData $input): FetchSubscriptionResponseData
    {
        $response = $this->connector->send(new FetchSubscriptionRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof FetchSubscriptionResponseData);

        return $dto;
    }

    public function __invoke(FetchSubscriptionInputData $input): FetchSubscriptionResponseData
    {
        return $this->execute($input);
    }
}
