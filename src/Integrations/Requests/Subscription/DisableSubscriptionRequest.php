<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Subscription;

use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\DisableSubscriptionResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class DisableSubscriptionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected DisableSubscriptionInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/subscription/disable';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->input->toRequestBody();
    }

    public function createDtoFromResponse(Response $response): DisableSubscriptionResponseData
    {
        /** @var mixed $payload */
        $payload = $response->json();
        /** @var array<string, mixed> $data */
        $data = is_array($payload) ? $payload : [];

        return DisableSubscriptionResponseData::fromPayload($data);
    }
}
