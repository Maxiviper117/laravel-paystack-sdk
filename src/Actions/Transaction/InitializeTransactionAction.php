<?php

namespace Maxiviper117\Paystack\Actions\Transaction;

use Maxiviper117\Paystack\Data\Transaction\InitializedTransactionData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\InitializeTransactionRequest;
use Maxiviper117\Paystack\Support\Amount;

final class InitializeTransactionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function execute(string $email, int|float|string $amount, array $options = []): InitializedTransactionData
    {
        $payload = array_merge($options, [
            'email' => $email,
            'amount' => Amount::toSubunit($amount),
        ]);

        $response = $this->connector->send(new InitializeTransactionRequest($payload));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof InitializedTransactionData);

        return $dto;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public static function run(string $email, int|float|string $amount, array $options = []): InitializedTransactionData
    {
        return app(self::class)->execute($email, $amount, $options);
    }
}
