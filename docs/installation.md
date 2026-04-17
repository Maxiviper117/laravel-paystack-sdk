# Installation

Install the package with Composer:

```bash
composer require maxiviper117/laravel-paystack-sdk
```

If you want to override the default package configuration, publish the config file:

```bash
php artisan vendor:publish --tag="paystack-config"
```

If you want to opt into the package-owned billing tables for the Billable layer and the local Paystack mirror, publish the package migrations too:

```bash
php artisan vendor:publish --tag="paystack-migrations"
php artisan migrate
```

## Laravel integration

The package service provider is auto-discovered. The facade alias is also registered for Laravel applications that use the package alias loader.

## Next step

After installation, configure your Paystack credentials and HTTP behavior in [Configuration](/configuration), then decide whether you want the optional [Billing Layer](/billing-layer).
