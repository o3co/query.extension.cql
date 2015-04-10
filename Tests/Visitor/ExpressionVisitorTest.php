<?php
namespace O3Co\Query\Extension\CQL\Tests\Visitor;

use O3Co\Query\Extension\CQL\Visitor\ExpressionVisitor;
use O3Co\Query\Extension\CQL\QueryBuilder;

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

    public function testVisit()
    {
        $visitor = new ExpressionVisitor();

        $qb = new QueryBuilder();
        $qb
                ->addWhere($qb->expr()->eq('foo', 'Foo'))
                ->addWhere($qb->expr()->eq('bar', 'Bar'))
                ->addOrder($qb->expr()->asc('foo'))
                ->setMaxResults(1)
                ->setFirstResult(1)
            ;
        $statement = $qb->getStatement();

        $visitor->visitStatement($statement);

        $this->assertEquals('and:(foo:=:Foo bar:=:Bar)', $visitor->getQueryComponent('query'));

        $this->assertEquals('+foo', $visitor->getQueryComponent('order'));
    }
}

