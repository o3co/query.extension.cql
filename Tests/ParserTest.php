<?php
namespace O3Co\Query\Extension\CQL\Tests;

use O3Co\Query\Extension\CQL\Parser;

use O3Co\Query\Query\Expr\ComparisonExpression;
use O3Co\Query\Query\Expr\TextComparisonExpression;
use O3Co\Query\Query\Expr\CollectionComparisonExpression;

class ParserTest extends \PHPUnit_Framework_TestCase 
{
    public function testParseFqlComparison()
    {
        $parser = new Parser(); 

        // Test Simple Value fql
        $expr = $parser->parseFql('field', 'value');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('value', $expr->getValue()->getValue());
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertEquals(ComparisonExpression::EQ, $expr->getOperator());

        // test comparison eq
        $expr = $parser->parseFql('field', '=:value');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('value', $expr->getValue()->getValue());
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertEquals(ComparisonExpression::EQ, $expr->getOperator());

        // test comparison ne
        $expr = $parser->parseFql('field', '!=:value');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('value', $expr->getValue()->getValue());
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertEquals(ComparisonExpression::NEQ, $expr->getOperator());

        // test comparison gt
        $expr = $parser->parseFql('field', '>:value');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('value', $expr->getValue()->getValue());
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertEquals(ComparisonExpression::GT, $expr->getOperator());

        // test comparison ge
        $expr = $parser->parseFql('field', '>=:value');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('value', $expr->getValue()->getValue());
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertEquals(ComparisonExpression::GTE, $expr->getOperator());

        // test comparison lt
        $expr = $parser->parseFql('field', '<:value');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('value', $expr->getValue()->getValue());
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertEquals(ComparisonExpression::LT, $expr->getOperator());

        // test comparison le
        $expr = $parser->parseFql('field', '<=:value');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('value', $expr->getValue()->getValue());
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertEquals(ComparisonExpression::LTE, $expr->getOperator());

        // test comparison match 
        $expr = $parser->parseFql('field', '*');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertNull($expr->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::IS_NOT, $expr->getOperator());

        // test comparison match 
        $expr = $parser->parseFql('field', '~');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertNull($expr->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::IS, $expr->getOperator());

        // test comparison match 
        $expr = $parser->parseFql('field', '%:*value*');
        $this->assertInstanceof('O3Co\Query\Query\Expr\TextComparisonExpression', $expr);
        $this->assertEquals('field', $expr->getField()->getName());
        $this->assertEquals('*value*', $expr->getValue()->getValue());
        $this->assertEquals(TextComparisonExpression::MATCH, $expr->getOperator());

        // Logical Expression Test
        $expr = $parser->parseFql('field', 'and:(foo !=:bar)');
        $this->assertInstanceof('O3Co\Query\Query\Expr\LogicalExpression', $expr);

        $this->assertEquals('field', $expr->getParts()[0]->getField()->getName());
        $this->assertEquals('foo', $expr->getParts()[0]->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::EQ, $expr->getParts()[0]->getOperator());

        $this->assertEquals('field', $expr->getParts()[1]->getField()->getName());
        $this->assertEquals('bar', $expr->getParts()[1]->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::NEQ, $expr->getParts()[1]->getOperator());

        // Or with phrase
        $expr = $parser->parseFql('field', 'or:("foo is Foo" !=:bar)');
        $this->assertInstanceof('O3Co\Query\Query\Expr\LogicalExpression', $expr);

        $this->assertEquals('field', $expr->getParts()[0]->getField()->getName());
        $this->assertEquals('foo is Foo', $expr->getParts()[0]->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::EQ, $expr->getParts()[0]->getOperator());

        $this->assertEquals('field', $expr->getParts()[1]->getField()->getName());
        $this->assertEquals('bar', $expr->getParts()[1]->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::NEQ, $expr->getParts()[1]->getOperator());

        // Not 
        $expr = $parser->parseFql('field', 'not:(!=:bar)');
        $this->assertInstanceof('O3Co\Query\Query\Expr\LogicalExpression', $expr);

        $this->assertEquals('field', $expr->getParts()[0]->getField()->getName());
        $this->assertEquals('bar', $expr->getParts()[0]->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::NEQ, $expr->getParts()[0]->getOperator());


        $expr = $parser->parseFql('field', 'range:[1,2}');
        $this->assertInstanceof('O3Co\Query\Query\Expr\RangeExpression', $expr);

        $this->assertEquals('field', $expr->getMinComparison()->getField()->getName());
        $this->assertEquals('1', $expr->getMinComparison()->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::GTE, $expr->getMinComparison()->getOperator());

        $this->assertEquals('field', $expr->getMaxComparison()->getField()->getName());
        $this->assertEquals('2', $expr->getMaxComparison()->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::LT, $expr->getMaxComparison()->getOperator());

        $expr = $parser->parseFql('field', 'in:[1,2, 3]');
        $this->assertInstanceof('O3Co\Query\Query\Expr\CollectionComparisonExpression', $expr);
        $this->assertEquals(CollectionComparisonExpression::IN, $expr->getOperator());

        $values = $expr->getValue()->getValue();
        $this->assertCount(3, $values);
        $this->assertContains(1, $values);
        $this->assertContains(2, $values);
        $this->assertContains(3, $values);


        // or:(1 2 3)
        $expr = $parser->parseFql('field', 'field:=:abc');
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);

        $expr = $parser->parseFql('field', 'or:(and:(>=:1 <:20) >:200)');
    }

    public function testParse()
    {
        $parser = new Parser(); 

        // Test Simple Value fql
        $query = $parser->parse('q=domain.field:=:foo&order=-field');
        $this->assertInstanceof('O3Co\Query\Query', $query);
        $stmt = $query->getStatement();
        $this->assertInstanceof('O3Co\Query\Query\Expr\Statement', $stmt);

        $expr = $stmt->getClause('condition')->getParts()[0];
        $this->assertEquals('foo', $expr->getValue()->getValue());
        $this->assertEquals('domain.field', $expr->getField()->getName());
        $this->assertEquals(ComparisonExpression::EQ, $expr->getOperator());


        // Complex Query
        $query = $parser->parse('q=and:(domain.field:=:foo bar:!=:bar)');
        $this->assertInstanceof('O3Co\Query\Query', $query);
        $stmt = $query->getStatement();
        $this->assertInstanceof('O3Co\Query\Query\Expr\Statement', $stmt);

        $exprs = $stmt->getClause('condition')->getParts();
        $this->assertCount(1, $exprs);

        $this->assertInstanceof('O3Co\Query\Query\Expr\LogicalExpression', $exprs[0]);
            
        $expr = $exprs[0]->getParts()[0];
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('domain.field', $expr->getField()->getName());
        $this->assertEquals('foo', $expr->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::EQ, $expr->getOperator());

        $expr = $exprs[0]->getParts()[1];
        $this->assertInstanceof('O3Co\Query\Query\Expr\ComparisonExpression', $expr);
        $this->assertEquals('bar', $expr->getField()->getName());
        $this->assertEquals('bar', $expr->getValue()->getValue());
        $this->assertEquals(ComparisonExpression::NEQ, $expr->getOperator());
    }
}

