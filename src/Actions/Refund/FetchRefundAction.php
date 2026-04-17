<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Refund;

use Maxiviper117\Paystack\Data\Input\Refund\FetchRefundInputData;
use Maxiviper117\Paystack\Data\Output\Refund\FetchRefundResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Refund\FetchRefundRequest;

/**
 * Fetch a refund on the integration.
 *
 * @see https://paystack.com/docs/api/refund/
 */
final class FetchRefundAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(FetchRefundInputData $input): FetchRefundResponseData
    {
        $response = $this->connector->send(new FetchRefundRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof FetchRefundResponseData);

        return $dto;
    }

    public function __invoke(FetchRefundInputData $input): FetchRefundResponseData
    {
        return $this->execute($input);
    }
}
