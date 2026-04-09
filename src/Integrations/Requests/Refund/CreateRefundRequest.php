<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Refund;

use Maxiviper117\Paystack\Data\Input\Refund\CreateRefundInputData;
use Maxiviper117\Paystack\Data\Output\Refund\CreateRefundResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateRefundRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected CreateRefundInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/refund';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->input->toRequestBody();
    }

    public function createDtoFromResponse(Response $response): CreateRefundResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return CreateRefundResponseData::fromPayload($payload);
    }
}
