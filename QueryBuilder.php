<?php
namespace O3Co\Query\Extension\CQL;

use O3Co\Query\Query\SimpleQueryBuilder;

/**
 * QueryBuilder 
 *   Alias of SimpleQueryBuidler to build Query with CQL Persister. 
 *   Use this QueryBuilder if you need to generate CQL Query 
 * @uses SimpleQueryBuilder
 * @package { PACKAGE }
 * @copyright Copyrights (c) 1o1.co.jp, All Rights Reserved.
 * @author Yoshi<yoshi@1o1.co.jp> 
 * @license { LICENSE }
 */
class QueryBuilder extends SimpleQueryBuilder 
{
    public function __construct()
    {
        parent::__construct(new CQLPersister());
    }
}

