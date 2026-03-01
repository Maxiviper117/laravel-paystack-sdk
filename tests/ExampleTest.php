<?php

use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\PaystackManager;

it('registers the manager in the container', function () {
    $manager = app(PaystackManager::class);
    $alias = app('paystack');
    $facadeRoot = Paystack::getFacadeRoot();

    $this->assertSame($manager, app(PaystackManager::class));
    $this->assertSame($manager, $alias);
    $this->assertInstanceOf(PaystackManager::class, $facadeRoot);
});
