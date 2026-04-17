<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Transaction;

use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\VerifyTransactionResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\VerifyTransactionRequest;

final class VerifyTransactionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(VerifyTransactionInputData $input): VerifyTransactionResponseData
    {
        $response = $this->connector->send(new VerifyTransactionRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof VerifyTransactionResponseData);

        return $dto;
    }

    public function __invoke(VerifyTransactionInputData $input): VerifyTransactionResponseData
    {
        return $this->execute($input);
    }
}
