<?php

namespace Maxiviper117\Paystack\Actions\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\FetchDisputeInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\FetchDisputeResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\FetchDisputeRequest;

/**
 * Fetch a single dispute by ID.
 *
 * @see https://paystack.com/docs/api/dispute/
 */
final class FetchDisputeAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(FetchDisputeInputData $input): FetchDisputeResponseData
    {
        $response = $this->connector->send(new FetchDisputeRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof FetchDisputeResponseData);

        return $dto;
    }

    public function __invoke(FetchDisputeInputData $input): FetchDisputeResponseData
    {
        return $this->execute($input);
    }
}
