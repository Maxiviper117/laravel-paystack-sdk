<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Transaction;

use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\ListTransactionsResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListTransactionsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ListTransactionsInputData $input
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
        return $this->input->toRequestQuery();
    }

    public function createDtoFromResponse(Response $response): ListTransactionsResponseData
    {
        $data = $response->json('data');
        $metaData = $response->json('meta');

        /** @var array<int, array<string, mixed>> $payload */
        $payload = is_array($data) ? array_values(array_filter($data, is_array(...))) : [];
        /** @var array<string, mixed> $meta */
        $meta = is_array($metaData) ? $metaData : [];

        return ListTransactionsResponseData::fromPayload(
            payload: $payload,
            meta: $meta
        );
    }
}
