<?php

namespace App\Filters;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class LikedPostFilter extends ApiFilter
{
    protected $safeParms = [
        'userId' => ['eq'],
        'postId' => ['eq']
    ];

    protected $columnMap = [
        'userId' => 'user_id',
        'postId' => 'post_id'
    ];

    protected $operatorMap = [
        'eq' => '='
    ];
}
