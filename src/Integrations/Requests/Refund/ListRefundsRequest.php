<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Refund;

use Maxiviper117\Paystack\Data\Input\Refund\ListRefundsInputData;
use Maxiviper117\Paystack\Data\Output\Refund\ListRefundsResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListRefundsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ListRefundsInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/refund';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return $this->input->toRequestQuery();
    }

    public function createDtoFromResponse(Response $response): ListRefundsResponseData
    {
        $data = $response->json('data');
        $metaData = $response->json('meta');

        /** @var array<int, array<string, mixed>> $payload */
        $payload = \is_array($data) ? array_values(array_filter($data, \is_array(...))) : [];
        /** @var array<string, mixed> $meta */
        $meta = \is_array($metaData) ? $metaData : [];

        return ListRefundsResponseData::fromPayload(
            payload: $payload,
            meta: $meta,
        );
    }
}
