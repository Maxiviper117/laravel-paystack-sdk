<?php

namespace Maxiviper117\Paystack\Actions\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\ListDisputesInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\ExportDisputesResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\ExportDisputesRequest;

/**
 * Export disputes as a downloadable CSV link.
 *
 * @see https://paystack.com/docs/api/dispute/
 */
final class ExportDisputesAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ListDisputesInputData $input): ExportDisputesResponseData
    {
        $response = $this->connector->send(new ExportDisputesRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ExportDisputesResponseData);

        return $dto;
    }

    public function __invoke(ListDisputesInputData $input): ExportDisputesResponseData
    {
        return $this->execute($input);
    }
}
