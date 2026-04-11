<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\ListSubscriptionsResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\ListSubscriptionsRequest;

/**
 * List subscriptions available on the integration.
 *
 * @see https://paystack.com/docs/api/subscription/#list-subscriptions
 */
final class ListSubscriptionsAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ListSubscriptionsInputData $input): ListSubscriptionsResponseData
    {
        $response = $this->connector->send(new ListSubscriptionsRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ListSubscriptionsResponseData);

        return $dto;
    }

    public function __invoke(ListSubscriptionsInputData $input): ListSubscriptionsResponseData
    {
        return $this->execute($input);
    }
}
