<?php
namespace O3Co\Query\Extension\CQL\Tests;

use O3Co\Query\Extension\CQL\Parser;

use O3Co\Query\Query\Term\ComparisonExpression;
use O3Co\Query\Query\Term\TextComparisonExpression;
use O3Co\Query\Query\Term\CollectionComparisonExpression;

class ParserTest extends \PHPUnit_Framework_TestCase 
{
	public function testParseFqlComparison()
	{
		$parser = new Parser(); 

		// Test Simple Value fql
		$expr = $parser->parseFql('field', 'value');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('value', $expr->getValue()->getValue());
		$this->assertEquals('field', $expr->getField());
		$this->assertEquals(ComparisonExpression::EQ, $expr->getOperator());

		// test comparison eq
		$expr = $parser->parseFql('field', '=:value');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('value', $expr->getValue()->getValue());
		$this->assertEquals('field', $expr->getField());
		$this->assertEquals(ComparisonExpression::EQ, $expr->getOperator());

		// test comparison ne
		$expr = $parser->parseFql('field', '!=:value');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('value', $expr->getValue()->getValue());
		$this->assertEquals('field', $expr->getField());
		$this->assertEquals(ComparisonExpression::NEQ, $expr->getOperator());

		// test comparison gt
		$expr = $parser->parseFql('field', '>:value');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('value', $expr->getValue()->getValue());
		$this->assertEquals('field', $expr->getField());
		$this->assertEquals(ComparisonExpression::GT, $expr->getOperator());

		// test comparison ge
		$expr = $parser->parseFql('field', '>=:value');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('value', $expr->getValue()->getValue());
		$this->assertEquals('field', $expr->getField());
		$this->assertEquals(ComparisonExpression::GTE, $expr->getOperator());

		// test comparison lt
		$expr = $parser->parseFql('field', '<:value');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('value', $expr->getValue()->getValue());
		$this->assertEquals('field', $expr->getField());
		$this->assertEquals(ComparisonExpression::LT, $expr->getOperator());

		// test comparison le
		$expr = $parser->parseFql('field', '<=:value');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('value', $expr->getValue()->getValue());
		$this->assertEquals('field', $expr->getField());
		$this->assertEquals(ComparisonExpression::LTE, $expr->getOperator());

		// test comparison match 
		$expr = $parser->parseFql('field', '*');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('field', $expr->getField());
		$this->assertNull($expr->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::IS_NOT, $expr->getOperator());

		// test comparison match 
		$expr = $parser->parseFql('field', '~');
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('field', $expr->getField());
		$this->assertNull($expr->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::IS, $expr->getOperator());

		// test comparison match 
		$expr = $parser->parseFql('field', '%:*value*');
		$this->assertInstanceof('O3Co\Query\Query\Term\TextComparisonExpression', $expr);
		$this->assertEquals('field', $expr->getField());
		$this->assertEquals('*value*', $expr->getValue()->getValue());
		$this->assertEquals(TextComparisonExpression::MATCH, $expr->getOperator());

		// Logical Expression Test
		$expr = $parser->parseFql('field', 'and:(foo !=:bar)');
		$this->assertInstanceof('O3Co\Query\Query\Term\LogicalExpression', $expr);

		$this->assertEquals('field', $expr->getTerms()[0]->getField());
		$this->assertEquals('foo', $expr->getTerms()[0]->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::EQ, $expr->getTerms()[0]->getOperator());

		$this->assertEquals('field', $expr->getTerms()[1]->getField());
		$this->assertEquals('bar', $expr->getTerms()[1]->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::NEQ, $expr->getTerms()[1]->getOperator());

		// Or with phrase
		$expr = $parser->parseFql('field', 'or:("foo is Foo" !=:bar)');
		$this->assertInstanceof('O3Co\Query\Query\Term\LogicalExpression', $expr);

		$this->assertEquals('field', $expr->getTerms()[0]->getField());
		$this->assertEquals('foo is Foo', $expr->getTerms()[0]->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::EQ, $expr->getTerms()[0]->getOperator());

		$this->assertEquals('field', $expr->getTerms()[1]->getField());
		$this->assertEquals('bar', $expr->getTerms()[1]->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::NEQ, $expr->getTerms()[1]->getOperator());

		// Not 
		$expr = $parser->parseFql('field', 'not:(!=:bar)');
		$this->assertInstanceof('O3Co\Query\Query\Term\LogicalExpression', $expr);

		$this->assertEquals('field', $expr->getTerms()[0]->getField());
		$this->assertEquals('bar', $expr->getTerms()[0]->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::NEQ, $expr->getTerms()[0]->getOperator());


		$expr = $parser->parseFql('field', 'range:[1,2}');
		$this->assertInstanceof('O3Co\Query\Query\Term\RangeExpression', $expr);

		$this->assertEquals('field', $expr->getMinComparison()->getField());
		$this->assertEquals('1', $expr->getMinComparison()->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::GTE, $expr->getMinComparison()->getOperator());

		$this->assertEquals('field', $expr->getMaxComparison()->getField());
		$this->assertEquals('2', $expr->getMaxComparison()->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::LT, $expr->getMaxComparison()->getOperator());

		$expr = $parser->parseFql('field', 'in:[1,2, 3]');
		$this->assertInstanceof('O3Co\Query\Query\Term\CollectionComparisonExpression', $expr);
		$this->assertEquals(CollectionComparisonExpression::IN, $expr->getOperator());

        $values = $expr->getValue()->getValue();
		$this->assertCount(3, $values);
		$this->assertContains(1, $values);
		$this->assertContains(2, $values);
		$this->assertContains(3, $values);



		// exception occured, cause fql not support to have field
		$expr = $parser->parseFql('field', 'field:=:abc');
	}

	public function testParse()
	{
		$parser = new Parser(); 

		// Test Simple Value fql
		$stmt = $parser->parse('q=domain.field:=:foo&order=-field');
		$this->assertInstanceof('O3Co\Query\Query\Term\Statement', $stmt);

		$expr = $stmt->getClause('condition')->getTerms()[0];
		$this->assertEquals('foo', $expr->getValue()->getValue());
		$this->assertEquals('domain.field', $expr->getField());
		$this->assertEquals(ComparisonExpression::EQ, $expr->getOperator());


		// Complex Query
		$stmt = $parser->parse('q=and:(domain.field:=:foo bar:!=:bar)');
		
		$this->assertInstanceof('O3Co\Query\Query\Term\Statement', $stmt);

		$exprs = $stmt->getClause('condition')->getTerms();
		$this->assertCount(1, $exprs);

		$this->assertInstanceof('O3Co\Query\Query\Term\LogicalExpression', $exprs[0]);
			
		$expr = $exprs[0]->getTerms()[0];
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('domain.field', $expr->getField());
		$this->assertEquals('foo', $expr->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::EQ, $expr->getOperator());

		$expr = $exprs[0]->getTerms()[1];
		$this->assertInstanceof('O3Co\Query\Query\Term\ComparisonExpression', $expr);
		$this->assertEquals('bar', $expr->getField());
		$this->assertEquals('bar', $expr->getValue()->getValue());
		$this->assertEquals(ComparisonExpression::NEQ, $expr->getOperator());

	}

}

