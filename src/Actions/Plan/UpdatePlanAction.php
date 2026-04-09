<?php

namespace Maxiviper117\Paystack\Actions\Plan;

use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Data\Output\Plan\UpdatePlanResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Plan\UpdatePlanRequest;

/**
 * Update an existing plan on the Paystack integration.
 *
 * @see https://paystack.com/docs/api/plan/#update-plan
 */
final class UpdatePlanAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(UpdatePlanInputData $input): UpdatePlanResponseData
    {
        $response = $this->connector->send(new UpdatePlanRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof UpdatePlanResponseData);

        return $dto;
    }

    public function __invoke(UpdatePlanInputData $input): UpdatePlanResponseData
    {
        return $this->execute($input);
    }
}
