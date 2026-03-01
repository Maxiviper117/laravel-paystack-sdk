<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Transaction;

use Maxiviper117\Paystack\Data\Transaction\TransactionListData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListTransactionsRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        protected array $filters = []
    ) {}

    public function resolveEndpoint(): string
    {
        return '/transaction';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return $this->filters;
    }

    public function createDtoFromResponse(Response $response): TransactionListData
    {
        $data = $response->json('data');
        $metaData = $response->json('meta');

        /** @var array<int, array<string, mixed>> $payload */
        $payload = is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
        /** @var array<string, mixed> $meta */
        $meta = is_array($metaData) ? $metaData : [];

        return TransactionListData::fromPayload(
            payload: $payload,
            meta: $meta
        );
    }
}
