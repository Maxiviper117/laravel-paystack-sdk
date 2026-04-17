<?php

declare(strict_types=1);

use Maxiviper117\Paystack\Support\Amount;

it('converts integer amounts to subunits', function () {
    expect(Amount::toSubunit(1500))->toBe(150000);
});

it('converts decimal amounts to subunits', function () {
    expect(Amount::toSubunit(15.75))->toBe(1575);
});

it('converts numeric strings to subunits', function () {
    expect(Amount::toSubunit('19.99'))->toBe(1999);
});

it('rejects negative amounts', function () {
    Amount::toSubunit(-4);
})->throws(InvalidArgumentException::class);

it('rejects non numeric amounts', function () {
    Amount::toSubunit('abc');
})->throws(InvalidArgumentException::class);
