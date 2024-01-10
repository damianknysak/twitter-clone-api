<?php

namespace App\Filters;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class FollowerFilter extends ApiFilter
{
    protected $safeParms = [
        'userId' => ['eq'],
        'followerId' => ['eq']
    ];

    protected $columnMap = [
        'userId' => 'user_id',
        'followerId' => 'follower_id'
    ];

    protected $operatorMap = [
        'eq' => '='
    ];
}
