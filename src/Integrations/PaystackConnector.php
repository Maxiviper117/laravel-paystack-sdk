<?php

namespace Maxiviper117\Paystack\Integrations;

use Maxiviper117\Paystack\Exceptions\PaystackConfigurationException;
use Saloon\Http\Connector;

class PaystackConnector extends Connector
{
    public ?int $tries;

    public ?int $retryInterval;

    public function __construct(
        protected string $secretKey,
        protected string $baseUrl,
        protected int $timeout = 30,
        protected int $connectTimeout = 10,
        int $retryTimes = 2,
        int $retrySleepMs = 250,
        protected bool $throwOnApiError = true,
    ) {
        if ($this->secretKey === '') {
            throw new PaystackConfigurationException('The Paystack secret key is not configured.');
        }

        $this->tries = $retryTimes;
        $this->retryInterval = $retrySleepMs;
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

    public function defaultConfig(): array
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
