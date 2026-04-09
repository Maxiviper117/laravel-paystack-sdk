<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\GetDisputeUploadUrlInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\GetDisputeUploadUrlResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetDisputeUploadUrlRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected GetDisputeUploadUrlInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/dispute/'.rawurlencode((string) $this->input->id).'/upload_url';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return $this->input->toRequestQuery();
    }

    public function createDtoFromResponse(Response $response): GetDisputeUploadUrlResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return GetDisputeUploadUrlResponseData::fromPayload($payload);
    }
}
