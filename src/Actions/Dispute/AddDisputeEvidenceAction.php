<?php

namespace Maxiviper117\Paystack\Actions\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\AddDisputeEvidenceInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\AddDisputeEvidenceResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\AddDisputeEvidenceRequest;

/**
 * Add evidence for a dispute.
 *
 * @see https://paystack.com/docs/api/dispute/
 */
final class AddDisputeEvidenceAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(AddDisputeEvidenceInputData $input): AddDisputeEvidenceResponseData
    {
        $response = $this->connector->send(new AddDisputeEvidenceRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof AddDisputeEvidenceResponseData);

        return $dto;
    }

    public function __invoke(AddDisputeEvidenceInputData $input): AddDisputeEvidenceResponseData
    {
        return $this->execute($input);
    }
}
