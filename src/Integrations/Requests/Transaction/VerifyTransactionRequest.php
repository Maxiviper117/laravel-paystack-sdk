<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Transaction;

use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\VerifyTransactionResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class VerifyTransactionRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected VerifyTransactionInputData $input
    ) {}

    public function resolveEndpoint(): string
    {
        return '/transaction/verify/'.$this->input->reference;
    }

    public function createDtoFromResponse(Response $response): VerifyTransactionResponseData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = \is_array($data) ? $data : [];

        return VerifyTransactionResponseData::fromPayload($payload);
    }
}
