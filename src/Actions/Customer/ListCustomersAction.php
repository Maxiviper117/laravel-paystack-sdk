<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Output\Customer\ListCustomersResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\ListCustomersRequest;

/**
 * List customers available on the integration.
 *
 * @see https://paystack.com/docs/api/customer/#list-customer
 */
final class ListCustomersAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ListCustomersInputData $input): ListCustomersResponseData
    {
        $response = $this->connector->send(new ListCustomersRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ListCustomersResponseData);

        return $dto;
    }

    public function __invoke(ListCustomersInputData $input): ListCustomersResponseData
    {
        return $this->execute($input);
    }
}
