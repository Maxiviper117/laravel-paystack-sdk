<?php

namespace Maxiviper117\Paystack\Actions\Transaction;

use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\FetchTransactionRequest;

final class FetchTransactionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(int|string $idOrReference): TransactionData
    {
        $response = $this->connector->send(new FetchTransactionRequest($idOrReference));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof TransactionData);

        return $dto;
    }

    public static function run(int|string $idOrReference): TransactionData
    {
        return app(self::class)->execute($idOrReference);
    }
}
