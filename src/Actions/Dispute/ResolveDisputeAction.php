<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\ResolveDisputeInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\ResolveDisputeResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\ResolveDisputeRequest;

/**
 * Resolve a dispute on the integration.
 *
 * @see https://paystack.com/docs/api/dispute/
 */
final class ResolveDisputeAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ResolveDisputeInputData $input): ResolveDisputeResponseData
    {
        $response = $this->connector->send(new ResolveDisputeRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ResolveDisputeResponseData);

        return $dto;
    }

    public function __invoke(ResolveDisputeInputData $input): ResolveDisputeResponseData
    {
        return $this->execute($input);
    }
}
