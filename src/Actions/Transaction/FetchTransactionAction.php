<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Transaction;

use Maxiviper117\Paystack\Data\Input\Transaction\FetchTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\FetchTransactionResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\FetchTransactionRequest;

final class FetchTransactionAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(FetchTransactionInputData $input): FetchTransactionResponseData
    {
        $response = $this->connector->send(new FetchTransactionRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof FetchTransactionResponseData);

        return $dto;
    }

    public function __invoke(FetchTransactionInputData $input): FetchTransactionResponseData
    {
        return $this->execute($input);
    }
}
