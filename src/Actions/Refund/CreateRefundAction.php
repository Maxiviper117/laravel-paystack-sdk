<?php

namespace Maxiviper117\Paystack\Actions\Refund;

use Maxiviper117\Paystack\Data\Input\Refund\CreateRefundInputData;
use Maxiviper117\Paystack\Data\Output\Refund\CreateRefundResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Refund\CreateRefundRequest;

/**
 * Create a refund on the integration.
 *
 * @see https://paystack.com/docs/api/refund/
 */
final class CreateRefundAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(CreateRefundInputData $input): CreateRefundResponseData
    {
        $response = $this->connector->send(new CreateRefundRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof CreateRefundResponseData);

        return $dto;
    }

    public function __invoke(CreateRefundInputData $input): CreateRefundResponseData
    {
        return $this->execute($input);
    }
}
