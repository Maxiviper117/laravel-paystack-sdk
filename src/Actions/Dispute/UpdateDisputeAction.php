<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\UpdateDisputeInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\UpdateDisputeResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\UpdateDisputeRequest;

/**
 * Update a dispute's refund details.
 *
 * @see https://paystack.com/docs/api/dispute/
 */
final class UpdateDisputeAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(UpdateDisputeInputData $input): UpdateDisputeResponseData
    {
        $response = $this->connector->send(new UpdateDisputeRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof UpdateDisputeResponseData);

        return $dto;
    }

    public function __invoke(UpdateDisputeInputData $input): UpdateDisputeResponseData
    {
        return $this->execute($input);
    }
}
