<?php

namespace Maxiviper117\Paystack\Facades;

use Illuminate\Support\Facades\Facade;

class Paystack extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'paystack';
    }
}
