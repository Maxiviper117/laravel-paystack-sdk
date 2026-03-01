<?php

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\UpdateCustomerRequest;

final class UpdateCustomerAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(string $customerCode, array $attributes): CustomerData
    {
        $response = $this->connector->send(new UpdateCustomerRequest($customerCode, $attributes));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof CustomerData);

        return $dto;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function run(string $customerCode, array $attributes): CustomerData
    {
        return app(self::class)->execute($customerCode, $attributes);
    }
}
