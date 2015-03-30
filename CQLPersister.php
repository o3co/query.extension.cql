<?php
namespace O3Co\Query\Extension\CQL;

use O3Co\Query\Extension\Http\HttpPersister;
use O3Co\Query\Extension\Http\Client as HttpClient;
use O3Co\Query\Extension\CQL\Visitor\ExpressionVisitor as CqlExpressionVisitor;

/**
 * CQLPersister 
 *   CQLPersister is a HttpPersister with CQL ExpressonVisitor 
 *   
 * @uses HttpPersister
 * @package { PACKAGE }
 * @copyright Copyrights (c) 1o1.co.jp, All Rights Reserved.
 * @author Yoshi<yoshi@1o1.co.jp> 
 * @license MIT
 */
class CQLPersister extends HttpPersister 
{
    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct(HttpClient $client = null)
    {
        parent::__construct(new CqlExpressionVisitor());

        if($client)
            $this->setClient($client);
    }
}

