<?php
namespace O3Co\Query\Extension\CQL;

use O3Co\Query\Query\SimpleQueryBuilder;
use O3Co\Query\Extension\Http\Client;

/**
 * QueryBuilder 
 *   Alias of SimpleQueryBuidler to build Query with CQL Persister. 
 *   Use this QueryBuilder if you need to generate CQL Query 
 * @uses SimpleQueryBuilder
 * @package \O3Co\Query
 * @copyright Copyrights (c) 1o1.co.jp, All Rights Reserved.
 * @author Yoshi<yoshi@1o1.co.jp> 
 * @license MIT
 */
class QueryBuilder extends SimpleQueryBuilder 
{
    public function __construct(Client $client = null)
    {
        parent::__construct(new CQLPersister($client));
    }
}

