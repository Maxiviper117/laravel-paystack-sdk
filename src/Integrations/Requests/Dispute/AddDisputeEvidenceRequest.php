<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\AddDisputeEvidenceInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\AddDisputeEvidenceResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class AddDisputeEvidenceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected AddDisputeEvidenceInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/dispute/'.rawurlencode((string) $this->input->id).'/evidence';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->input->toRequestBody();
    }

    public function createDtoFromResponse(Response $response): AddDisputeEvidenceResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = \is_array($data) ? $data : [];

        return AddDisputeEvidenceResponseData::fromPayload($payload);
    }
}
