<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Output\Customer\SetCustomerRiskActionResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\SetCustomerRiskActionRequest;

/**
 * Set the customer's risk action to allow, deny, or default.
 *
 * @see https://paystack.com/docs/api/customer/#whitelist-blacklist-customer
 */
final class SetCustomerRiskAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(SetCustomerRiskActionInputData $input): SetCustomerRiskActionResponseData
    {
        $response = $this->connector->send(new SetCustomerRiskActionRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof SetCustomerRiskActionResponseData);

        return $dto;
    }

    public function __invoke(SetCustomerRiskActionInputData $input): SetCustomerRiskActionResponseData
    {
        return $this->execute($input);
    }
}
