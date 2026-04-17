<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Refund;

use Maxiviper117\Paystack\Data\Input\Refund\RetryRefundInputData;
use Maxiviper117\Paystack\Data\Output\Refund\RetryRefundResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Refund\RetryRefundRequest;

/**
 * Retry a refund with customer bank details.
 *
 * @see https://paystack.com/docs/api/refund/
 */
final class RetryRefundAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(RetryRefundInputData $input): RetryRefundResponseData
    {
        $response = $this->connector->send(new RetryRefundRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof RetryRefundResponseData);

        return $dto;
    }

    public function __invoke(RetryRefundInputData $input): RetryRefundResponseData
    {
        return $this->execute($input);
    }
}
