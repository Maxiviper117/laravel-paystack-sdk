<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\GetDisputeUploadUrlInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\GetDisputeUploadUrlResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\GetDisputeUploadUrlRequest;

/**
 * Generate an upload URL for dispute evidence attachments.
 *
 * @see https://paystack.com/docs/api/dispute/
 */
final class GetDisputeUploadUrlAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(GetDisputeUploadUrlInputData $input): GetDisputeUploadUrlResponseData
    {
        $response = $this->connector->send(new GetDisputeUploadUrlRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof GetDisputeUploadUrlResponseData);

        return $dto;
    }

    public function __invoke(GetDisputeUploadUrlInputData $input): GetDisputeUploadUrlResponseData
    {
        return $this->execute($input);
    }
}
