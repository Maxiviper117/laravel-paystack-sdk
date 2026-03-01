<?php

use Maxiviper117\Paystack\Exceptions\PaystackConfigurationException;
use Maxiviper117\Paystack\Integrations\PaystackConnector;

it('throws when the secret key is missing', function () {
    new PaystackConnector(
        secretKey: '',
        baseUrl: 'https://api.paystack.co',
    );
})->throws(PaystackConfigurationException::class);

it('exposes default headers and config through the connector', function () {
    $connector = new class (
        secretKey: 'sk_test_123',
        baseUrl: 'https://api.paystack.co',
        timeout: 45,
        connectTimeout: 15,
        tries: 3,
        retryInterval: 500,
        throwOnApiError: false,
    ) extends PaystackConnector {
        /**
         * @return array<string, int>
         */
        public function exposedDefaultConfig(): array
        {
            return $this->defaultConfig();
        }
    };

    expect($connector->resolveBaseUrl())->toBe('https://api.paystack.co')
        ->and($connector->exposedDefaultConfig())->toBe([
            'timeout' => 45,
            'connect_timeout' => 15,
        ])
        ->and($connector->throwsOnApiError())->toBeFalse()
        ->and($connector->tries)->toBe(3)
        ->and($connector->retryInterval)->toBe(500);
});
