<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Output\Customer\ListCustomersResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListCustomersRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ListCustomersInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/customer';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return $this->input->toRequestQuery();
    }

    public function createDtoFromResponse(Response $response): ListCustomersResponseData
    {
        $data = $response->json('data');
        $metaData = $response->json('meta');

        /** @var array<int, array<string, mixed>> $payload */
        $payload = is_array($data) ? array_values(array_filter($data, is_array(...))) : [];
        /** @var array<string, mixed> $meta */
        $meta = is_array($metaData) ? $metaData : [];

        return ListCustomersResponseData::fromPayload(
            payload: $payload,
            meta: $meta
        );
    }
}
