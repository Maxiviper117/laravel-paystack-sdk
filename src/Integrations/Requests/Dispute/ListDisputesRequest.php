<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Dispute;

use Maxiviper117\Paystack\Data\Input\Dispute\ListDisputesInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\ListDisputesResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListDisputesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ListDisputesInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/dispute';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return $this->input->toRequestQuery();
    }

    public function createDtoFromResponse(Response $response): ListDisputesResponseData
    {
        $data = $response->json('data');
        $metaData = $response->json('meta');

        /** @var array<int, array<string, mixed>> $payload */
        $payload = \is_array($data) ? array_values(array_filter($data, \is_array(...))) : [];
        /** @var array<string, mixed> $meta */
        $meta = \is_array($metaData) ? $metaData : [];

        return ListDisputesResponseData::fromPayload(
            payload: $payload,
            meta: $meta
        );
    }
}
