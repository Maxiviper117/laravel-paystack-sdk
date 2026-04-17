<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\ListDisputesInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\ListDisputesResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\ListDisputesRequest;

/**
 * List disputes filed against the integration.
 *
 * @see https://paystack.com/docs/api/dispute/
 */
final class ListDisputesAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ListDisputesInputData $input): ListDisputesResponseData
    {
        $response = $this->connector->send(new ListDisputesRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ListDisputesResponseData);

        return $dto;
    }

    public function __invoke(ListDisputesInputData $input): ListDisputesResponseData
    {
        return $this->execute($input);
    }
}
