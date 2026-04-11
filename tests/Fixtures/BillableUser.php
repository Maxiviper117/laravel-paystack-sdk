<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Maxiviper117\Paystack\Concerns\Billable;

class BillableUser extends Model
{
    use Billable;

    protected $table = 'test_billable_users';

    protected $guarded = [];
}
