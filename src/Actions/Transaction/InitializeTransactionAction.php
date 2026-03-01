<?php

namespace Maxiviper117\Paystack\Actions\Transaction;

use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\InitializeTransactionResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\InitializeTransactionRequest;

final class InitializeTransactionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(InitializeTransactionInputData $input): InitializeTransactionResponseData
    {
        $response = $this->connector->send(new InitializeTransactionRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof InitializeTransactionResponseData);

        return $dto;
    }

    public function __invoke(InitializeTransactionInputData $input): InitializeTransactionResponseData
    {
        return $this->execute($input);
    }
}
