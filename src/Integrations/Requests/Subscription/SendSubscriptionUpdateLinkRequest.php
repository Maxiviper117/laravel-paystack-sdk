<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\SendSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\SendSubscriptionUpdateLinkResponseData;
use Maxiviper117\Paystack\Support\Payload;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class SendSubscriptionUpdateLinkRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected SendSubscriptionUpdateLinkInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/subscription/'.$this->input->code.'/manage/email';
    }

    public function createDtoFromResponse(Response $response): SendSubscriptionUpdateLinkResponseData
    {
        /** @var mixed $payload */
        $payload = $response->json();

        /** @var array<string, mixed> $data */
        $data = is_array($payload) ? $payload : [];

        return new SendSubscriptionUpdateLinkResponseData(
            status: Payload::bool($data, 'status'),
            message: Payload::string($data, 'message'),
        );
    }
}
