<?php

namespace Maxiviper117\Paystack\Actions\Plan;

use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Data\Output\Plan\ListPlansResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Plan\ListPlansRequest;

final class ListPlansAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ListPlansInputData $input): ListPlansResponseData
    {
        $response = $this->connector->send(new ListPlansRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ListPlansResponseData);

        return $dto;
    }

    public function __invoke(ListPlansInputData $input): ListPlansResponseData
    {
        return $this->execute($input);
    }
}
