<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\FetchDisputeInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\FetchDisputeResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class FetchDisputeRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected FetchDisputeInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/dispute/'.rawurlencode((string) $this->input->id);
    }

    public function createDtoFromResponse(Response $response): FetchDisputeResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return FetchDisputeResponseData::fromPayload($payload);
    }
}
