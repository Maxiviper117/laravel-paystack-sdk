<?php

namespace Maxiviper117\Paystack\Integrations\Requests\Transaction;

use Maxiviper117\Paystack\Data\Transaction\VerificationData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class VerifyTransactionRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $reference
    ) {}

    public function resolveEndpoint(): string
    {
        return '/transaction/verify/'.$this->reference;
    }

    public function createDtoFromResponse(Response $response): VerificationData
    {
        $data = $response->json('data');

        /** @var array<string, mixed> $payload */
        $payload = is_array($data) ? $data : [];

        return VerificationData::fromPayload($payload);
    }
}
