<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\ListTransactionDisputesInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\ListTransactionDisputesResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListTransactionDisputesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ListTransactionDisputesInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/dispute/transaction/'.rawurlencode((string) $this->input->id);
    }

    public function createDtoFromResponse(Response $response): ListTransactionDisputesResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return ListTransactionDisputesResponseData::fromPayload($payload);
    }
}
