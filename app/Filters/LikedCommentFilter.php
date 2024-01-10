<?php

namespace App\Filters;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class LikedCommentFilter extends ApiFilter
{
    protected $safeParms = [
        'userId' => ['eq'],
        'commentId' => ['eq']
    ];

    protected $columnMap = [
        'userId' => 'user_id',
        'commentId' => 'comment_id'
    ];

    protected $operatorMap = [
        'eq' => '='
    ];
}
