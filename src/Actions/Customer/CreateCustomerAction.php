<?php

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\CreateCustomerRequest;

final class CreateCustomerAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(string $email, array $attributes = []): CustomerData
    {
        $payload = array_merge($attributes, ['email' => $email]);

        $response = $this->connector->send(new CreateCustomerRequest($payload));

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
    public static function run(string $email, array $attributes = []): CustomerData
    {
        return app(self::class)->execute($email, $attributes);
    }
}
