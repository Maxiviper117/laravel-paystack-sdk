<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\ValidateCustomerResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\ValidateCustomerRequest;

/**
 * Validate a customer's identity details.
 *
 * @see https://paystack.com/docs/api/customer/#validate-customer
 */
final class ValidateCustomerAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ValidateCustomerInputData $input): ValidateCustomerResponseData
    {
        $response = $this->connector->send(new ValidateCustomerRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ValidateCustomerResponseData);

        return $dto;
    }

    public function __invoke(ValidateCustomerInputData $input): ValidateCustomerResponseData
    {
        return $this->execute($input);
    }
}
