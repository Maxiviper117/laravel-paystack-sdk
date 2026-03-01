<?php

namespace Maxiviper117\Paystack\Actions\Customer;

use Maxiviper117\Paystack\Data\Customer\CustomerListData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\ListCustomersRequest;

final class ListCustomersAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): CustomerListData
    {
        $response = $this->connector->send(new ListCustomersRequest($filters));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof CustomerListData);

        return $dto;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public static function run(array $filters = []): CustomerListData
    {
        return app(self::class)->execute($filters);
    }
}
