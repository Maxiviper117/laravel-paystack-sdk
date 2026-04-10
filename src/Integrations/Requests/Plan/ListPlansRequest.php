<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Plan;

use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Data\Output\Plan\ListPlansResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListPlansRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ListPlansInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/plan';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return $this->input->toRequestQuery();
    }

    public function createDtoFromResponse(Response $response): ListPlansResponseData
    {
        $data = $response->json('data');
        $metaData = $response->json('meta');

        /** @var array<int, array<string, mixed>> $payload */
        $payload = \is_array($data) ? array_values(array_filter($data, \is_array(...))) : [];
        /** @var array<string, mixed> $meta */
        $meta = \is_array($metaData) ? $metaData : [];

        return ListPlansResponseData::fromPayload($payload, $meta);
    }
}
