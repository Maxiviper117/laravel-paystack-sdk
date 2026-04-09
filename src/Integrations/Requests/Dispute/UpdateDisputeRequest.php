<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\UpdateDisputeInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\UpdateDisputeResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateDisputeRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected UpdateDisputeInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/dispute/'.rawurlencode((string) $this->input->id);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->input->toRequestBody();
    }

    public function createDtoFromResponse(Response $response): UpdateDisputeResponseData
    {
        $data = $response->json('data');

        /** @var array<int, array<string, mixed>> $payload */
        $payload = is_array($data) ? array_values(array_filter($data, is_array(...))) : [];

        return UpdateDisputeResponseData::fromPayload($payload);
    }
}
