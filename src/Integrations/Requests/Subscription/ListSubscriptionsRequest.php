<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\ListSubscriptionsResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListSubscriptionsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ListSubscriptionsInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/subscription';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return $this->input->toRequestQuery();
    }

    public function createDtoFromResponse(Response $response): ListSubscriptionsResponseData
    {
        $data = $response->json('data');
        $metaData = $response->json('meta');

        /** @var array<int, array<string, mixed>> $payload */
        $payload = \is_array($data) ? array_values(array_filter($data, \is_array(...))) : [];
        /** @var array<string, mixed> $meta */
        $meta = \is_array($metaData) ? $metaData : [];

        return ListSubscriptionsResponseData::fromPayload($payload, $meta);
    }
}
