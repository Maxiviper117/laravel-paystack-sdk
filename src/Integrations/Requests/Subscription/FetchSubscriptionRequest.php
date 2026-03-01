<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\FetchSubscriptionResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class FetchSubscriptionRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected FetchSubscriptionInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/subscription/'.$this->input->idOrCode;
    }

    public function createDtoFromResponse(Response $response): FetchSubscriptionResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return FetchSubscriptionResponseData::fromPayload($payload);
    }
}
