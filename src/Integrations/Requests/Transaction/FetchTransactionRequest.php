<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Transaction;

use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class FetchTransactionRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int|string $idOrReference
    ) {}

    public function resolveEndpoint(): string
    {
        return '/transaction/'.$this->idOrReference;
    }

    public function createDtoFromResponse(Response $response): TransactionData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return TransactionData::fromPayload($payload);
    }
}
