<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\ValidateCustomerResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class ValidateCustomerRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected ValidateCustomerInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/customer/'.$this->input->customerCode.'/identification';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->input->toRequestBody();
    }

    public function createDtoFromResponse(Response $response): ValidateCustomerResponseData
    {
        /** @var mixed $data */
        $data = $response->json();

        /** @var array<string, mixed> $payload */
        $payload = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (\is_string($key)) {
                    $payload[$key] = $value;
                }
            }
        }

        return ValidateCustomerResponseData::fromPayload($payload);
    }
}
