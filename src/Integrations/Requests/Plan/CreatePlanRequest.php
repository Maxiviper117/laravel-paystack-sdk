<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Plan;

use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Output\Plan\CreatePlanResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreatePlanRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected CreatePlanInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/plan';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->input->toRequestBody();
    }

    public function createDtoFromResponse(Response $response): CreatePlanResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return CreatePlanResponseData::fromPayload($payload);
    }
}
