<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\ListDisputesInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\ExportDisputesResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ExportDisputesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ListDisputesInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/dispute/export';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return $this->input->toRequestQuery();
    }

    public function createDtoFromResponse(Response $response): ExportDisputesResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = \is_array($data) ? $data : [];

        return ExportDisputesResponseData::fromPayload($payload);
    }
}
