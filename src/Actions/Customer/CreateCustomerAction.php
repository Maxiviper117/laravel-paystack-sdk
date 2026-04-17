<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\CreateCustomerResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\CreateCustomerRequest;

/**
 * Create a new customer on the Paystack integration.
 *
 * @see https://paystack.com/docs/api/customer/#create-customer
 */
final class CreateCustomerAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(CreateCustomerInputData $input): CreateCustomerResponseData
    {
        $response = $this->connector->send(new CreateCustomerRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof CreateCustomerResponseData);

        return $dto;
    }

    public function __invoke(CreateCustomerInputData $input): CreateCustomerResponseData
    {
        return $this->execute($input);
    }
}
