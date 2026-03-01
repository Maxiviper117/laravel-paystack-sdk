<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('actions are final')
    ->expect('Maxiviper117\\Paystack\\Actions')
    ->classes()
    ->toBeFinal();

arch('actions do not depend on facades')
    ->expect('Maxiviper117\\Paystack\\Actions')
    ->not->toUse('Illuminate\\Support\\Facades');
