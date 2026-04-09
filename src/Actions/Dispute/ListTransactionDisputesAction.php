<?php

namespace Maxiviper117\Paystack\Actions\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\ListTransactionDisputesInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\ListTransactionDisputesResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\ListTransactionDisputesRequest;

/**
 * Fetch disputes attached to a specific transaction.
 *
 * @see https://paystack.com/docs/api/dispute/
 */
final class ListTransactionDisputesAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ListTransactionDisputesInputData $input): ListTransactionDisputesResponseData
    {
        $response = $this->connector->send(new ListTransactionDisputesRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ListTransactionDisputesResponseData);

        return $dto;
    }

    public function __invoke(ListTransactionDisputesInputData $input): ListTransactionDisputesResponseData
    {
        return $this->execute($input);
    }
}
