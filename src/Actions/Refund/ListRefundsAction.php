<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Refund;

use Maxiviper117\Paystack\Data\Input\Refund\ListRefundsInputData;
use Maxiviper117\Paystack\Data\Output\Refund\ListRefundsResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Refund\ListRefundsRequest;

/**
 * List refunds on the integration.
 *
 * @see https://paystack.com/docs/api/refund/
 */
final class ListRefundsAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ListRefundsInputData $input): ListRefundsResponseData
    {
        $response = $this->connector->send(new ListRefundsRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ListRefundsResponseData);

        return $dto;
    }

    public function __invoke(ListRefundsInputData $input): ListRefundsResponseData
    {
        return $this->execute($input);
    }
}
