<?php

namespace Maxiviper117\Paystack\Actions\Transaction;

use Maxiviper117\Paystack\Data\Transaction\VerificationData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\VerifyTransactionRequest;

final class VerifyTransactionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(string $reference): VerificationData
    {
        $response = $this->connector->send(new VerifyTransactionRequest($reference));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof VerificationData);

        return $dto;
    }

    public static function run(string $reference): VerificationData
    {
        return app(self::class)->execute($reference);
    }
}
