<?php

namespace Maxiviper117\Paystack\Actions\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\GenerateSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\GenerateSubscriptionUpdateLinkResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\GenerateSubscriptionUpdateLinkRequest;

/**
 * Generate a hosted update link for a subscription.
 *
 * @see https://paystack.com/docs/api/subscription/#generate-update-subscription-link
 */
final class GenerateSubscriptionUpdateLinkAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(GenerateSubscriptionUpdateLinkInputData $input): GenerateSubscriptionUpdateLinkResponseData
    {
        $response = $this->connector->send(new GenerateSubscriptionUpdateLinkRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof GenerateSubscriptionUpdateLinkResponseData);

        return $dto;
    }

    public function __invoke(GenerateSubscriptionUpdateLinkInputData $input): GenerateSubscriptionUpdateLinkResponseData
    {
        return $this->execute($input);
    }
}
