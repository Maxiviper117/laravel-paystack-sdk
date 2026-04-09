<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\FetchCustomerResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class FetchCustomerRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected FetchCustomerInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/customer/'.rawurlencode($this->input->emailOrCode);
    }

    public function createDtoFromResponse(Response $response): FetchCustomerResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return FetchCustomerResponseData::fromPayload($payload);
    }
}
