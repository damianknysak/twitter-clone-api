<?php

namespace App\Filters;

use App\Filters\ApiFilter;

class UserFilter extends ApiFilter
{
    protected $safeParms = [
        'id' => ['eq'],
        'name' => ['eq'],
        'nickname' => ['eq'],
        'email' => ['eq'],
    ];

    protected $columnMap = [];

    protected $operatorMap = [
        'eq' => '='
    ];
}
