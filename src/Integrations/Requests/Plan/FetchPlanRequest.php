<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Plan;

use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;
use Maxiviper117\Paystack\Data\Output\Plan\FetchPlanResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class FetchPlanRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected FetchPlanInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/plan/'.rawurlencode((string) $this->input->idOrCode);
    }

    public function createDtoFromResponse(Response $response): FetchPlanResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = \is_array($data) ? $data : [];

        return FetchPlanResponseData::fromPayload($payload);
    }
}
