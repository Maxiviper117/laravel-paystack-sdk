<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Refund;

use Maxiviper117\Paystack\Data\Input\Refund\RetryRefundInputData;
use Maxiviper117\Paystack\Data\Output\Refund\RetryRefundResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class RetryRefundRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected RetryRefundInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/refund/retry_with_customer_details/'.rawurlencode((string) $this->input->id);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->input->toRequestBody();
    }

    public function createDtoFromResponse(Response $response): RetryRefundResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = \is_array($data) ? $data : [];

        return RetryRefundResponseData::fromPayload($payload);
    }
}
