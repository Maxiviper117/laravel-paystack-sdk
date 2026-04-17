<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Integrations;

use Maxiviper117\Paystack\Exceptions\PaystackConfigurationException;
use Saloon\Http\Connector;

class PaystackConnector extends Connector
{
    public function __construct(
        protected string $secretKey,
        protected string $baseUrl,
        protected int $timeout = 30,
        protected int $connectTimeout = 10,
        public ?int $tries = 2,
        public ?int $retryInterval = 250,
        protected bool $throwOnApiError = true,
    ) {
        if ($this->secretKey === '') {
            throw new PaystackConfigurationException('The Paystack secret key is not configured.');
        }
    }

    public function resolveBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->secretKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
        ];
    }

    public function throwsOnApiError(): bool
    {
        return $this->throwOnApiError;
    }
}
