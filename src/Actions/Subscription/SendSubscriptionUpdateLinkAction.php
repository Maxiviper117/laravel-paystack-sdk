<?php

namespace Maxiviper117\Paystack\Actions\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\SendSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\SendSubscriptionUpdateLinkResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\SendSubscriptionUpdateLinkRequest;

/**
 * Email a hosted update link to the subscription customer.
 *
 * @see https://paystack.com/docs/api/subscription/#send-update-subscription-link
 */
final class SendSubscriptionUpdateLinkAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(SendSubscriptionUpdateLinkInputData $input): SendSubscriptionUpdateLinkResponseData
    {
        $response = $this->connector->send(new SendSubscriptionUpdateLinkRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof SendSubscriptionUpdateLinkResponseData);

        return $dto;
    }

    public function __invoke(SendSubscriptionUpdateLinkInputData $input): SendSubscriptionUpdateLinkResponseData
    {
        return $this->execute($input);
    }
}
