<?php

namespace Maxiviper117\Paystack\Actions\Plan;

use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;
use Maxiviper117\Paystack\Data\Output\Plan\FetchPlanResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Plan\FetchPlanRequest;

/**
 * Fetch a plan by ID or plan code.
 *
 * @see https://paystack.com/docs/api/plan/#fetch-plan
 */
final class FetchPlanAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(FetchPlanInputData $input): FetchPlanResponseData
    {
        $response = $this->connector->send(new FetchPlanRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof FetchPlanResponseData);

        return $dto;
    }

    public function __invoke(FetchPlanInputData $input): FetchPlanResponseData
    {
        return $this->execute($input);
    }
}
