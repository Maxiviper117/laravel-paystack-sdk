<?php

namespace Maxiviper117\Paystack\Actions\Plan;

use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Output\Plan\CreatePlanResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Plan\CreatePlanRequest;

final class CreatePlanAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(CreatePlanInputData $input): CreatePlanResponseData
    {
        $response = $this->connector->send(new CreatePlanRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof CreatePlanResponseData);

        return $dto;
    }

    public function __invoke(CreatePlanInputData $input): CreatePlanResponseData
    {
        return $this->execute($input);
    }
}
