<?php

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\FetchCustomerResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\FetchCustomerRequest;

/**
 * Fetch a customer by email address or customer code.
 *
 * @see https://paystack.com/docs/api/customer/#fetch-customer
 */
final class FetchCustomerAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(FetchCustomerInputData $input): FetchCustomerResponseData
    {
        $response = $this->connector->send(new FetchCustomerRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof FetchCustomerResponseData);

        return $dto;
    }

    public function __invoke(FetchCustomerInputData $input): FetchCustomerResponseData
    {
        return $this->execute($input);
    }
}
