<?php

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\UpdateCustomerRequest;

/**
 * Update an existing customer record.
 *
 * @see https://paystack.com/docs/api/customer/#update-customer
 */
final class UpdateCustomerAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(UpdateCustomerInputData $input): UpdateCustomerResponseData
    {
        $response = $this->connector->send(new UpdateCustomerRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof UpdateCustomerResponseData);

        return $dto;
    }

    public function __invoke(UpdateCustomerInputData $input): UpdateCustomerResponseData
    {
        return $this->execute($input);
    }
}
