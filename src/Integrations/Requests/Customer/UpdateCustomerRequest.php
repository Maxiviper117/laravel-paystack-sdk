<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateCustomerRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected UpdateCustomerInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/customer/'.$this->input->customerCode;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->input->toRequestBody();
    }

    public function createDtoFromResponse(Response $response): UpdateCustomerResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = \is_array($data) ? $data : [];

        return UpdateCustomerResponseData::fromPayload($payload);
    }
}
