<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\GenerateSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\GenerateSubscriptionUpdateLinkResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GenerateSubscriptionUpdateLinkRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected GenerateSubscriptionUpdateLinkInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/subscription/'.$this->input->code.'/manage/link';
    }

    public function createDtoFromResponse(Response $response): GenerateSubscriptionUpdateLinkResponseData
    {
        /** @var mixed $payload */
        $payload = $response->json();

        /** @var array<string, mixed> $data */
        $data = is_array($payload) ? $payload : [];

        return GenerateSubscriptionUpdateLinkResponseData::fromPayload($data);
    }
}
