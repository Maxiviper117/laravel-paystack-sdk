<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Refund;

use Maxiviper117\Paystack\Data\Input\Refund\FetchRefundInputData;
use Maxiviper117\Paystack\Data\Output\Refund\FetchRefundResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class FetchRefundRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected FetchRefundInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/refund/'.rawurlencode((string) $this->input->id);
    }

    public function createDtoFromResponse(Response $response): FetchRefundResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = \is_array($data) ? $data : [];

        return FetchRefundResponseData::fromPayload($payload);
    }
}
