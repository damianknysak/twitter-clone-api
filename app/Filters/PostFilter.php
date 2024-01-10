<?php

namespace App\Filters;

use App\Filters\ApiFilter;

class PostFilter extends ApiFilter
{
    protected $safeParms = [
        'authorId' => ['eq'],
        'postId' => ['eq'],
        'slug' => ['eq']
    ];

    protected $columnMap = [
        'authorId' => 'author_id',
        'postId' => 'id',
        'slug' => 'slug'
    ];

    protected $operatorMap = [
        'eq' => '='
    ];
}
