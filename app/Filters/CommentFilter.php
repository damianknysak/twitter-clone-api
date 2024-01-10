<?php

namespace App\Filters;

use App\Filters\ApiFilter;

class CommentFilter extends ApiFilter
{
    protected $safeParms = [
        'authorId' => ['eq'],
        'postId' => ['eq']
    ];

    protected $columnMap = [
        'authorId' => 'author_id',
        'postId' => 'post_id'
    ];

    protected $operatorMap = [
        'eq' => '='
    ];
}
