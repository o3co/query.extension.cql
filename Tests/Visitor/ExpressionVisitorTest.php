<?php
namespace O3Co\Query\Extension\CQL\Tests\Visitor;

use O3Co\Query\Extension\CQL\Visitor\ExpressionVisitor;

class ExpressionVisitorTest extends \PHPUnit_Framework_TestCase 
{

    public function testGetNativeQuery()
    {
        $visitor  = new ExpressionVisitor();

        $q = 'abc';
        $visitor->setQueryComponent('q', $q);
        $query = $visitor->getNativeQuery(array('urlencode' => true));
        $this->assertEquals('q=' . urlencode($q), $query);

        $query = $visitor->getNativeQuery(array('urlencode' => false));
        $this->assertEquals('q=' . $q, $query);

    }

}

