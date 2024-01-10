<?php

namespace App\Filters;

use App\Filters\ApiFilter;

class SearchFilter extends ApiFilter
{
    protected $safeParms = [
        'q' => ['eq'],
    ];

    protected $columnMap = [];

    protected $operatorMap = [
        'eq' => '='
    ];
}
