<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Transaction;

use Maxiviper117\Paystack\Data\Input\Transaction\FetchTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\FetchTransactionResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class FetchTransactionRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected FetchTransactionInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/transaction/'.$this->input->id;
    }

    public function createDtoFromResponse(Response $response): FetchTransactionResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = \is_array($data) ? $data : [];

        return FetchTransactionResponseData::fromPayload($payload);
    }
}
