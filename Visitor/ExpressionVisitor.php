<?php
namespace O3Co\Query\Extension\CQL\Visitor;

use O3Co\Query\Query\Visitor\ExpressionVisitor as BaseVisitor;
use O3Co\Query\Extension\CQL\Literals;
use O3Co\Query\Query\Term;

/**
 * ExpressionVisitor 
 *   CQL ExpressionVisitor is to generate Http based CQL native query from SimpleExpression. 
 * @uses BaseVisitor
 * @package { PACKAGE }
 * @copyright Copyrights (c) 1o1.co.jp, All Rights Reserved.
 * @author Yoshi<yoshi@1o1.co.jp> 
 * @license { LICENSE }
 */
class ExpressionVisitor extends BaseVisitor
{
    private $queryComponents = array();

    // PHP_QUERY_RFC1738 or PHP_QUERY_RFC3986
    private $encType = PHP_QUERY_RFC1738;

	public function getNativeQuery(array $options = array())
	{
		$query = http_build_query($this->queryComponents, null, null, $this->encType);

        if(isset($options['urlencode']) && !$options['urlencode']) {
            return urldecode($query);
        }

        return $query;
	}

	public function visitStatement(Term\Statement $statement)
	{
        $this->reset();

		// apply
        $this->queryComponents['q'] = $this->visitConditionalClause($statement->getClause('condition'));
        $this->queryComponents['order'] = $this->visitOrderClause($statement->getClause('order'));
	}

	public function visitConditionalClause(Term\ConditionalClause $clause)
	{
		foreach($clause->getTerms() as $term) {
			$terms[] = $term->dispatch($this);
		}

        return implode(' ', $terms);
	}

	public function visitOrderClause(Term\OrderClause $clause)
	{
        $exprs = array();
        foreach($clause->getExpressions() as $expr) {
            $exprs[] = $this->visitOrderExpression();
        }
        return implode(',', $exprs);
	}
    
    /**
     * visitLogicalExpression 
     * 
     * @param Term\LogicalExpression $expr 
     * @access public
     * @return void
     */
    public function visitLogicalExpression(Term\LogicalExpression $expr)
    {
        $exprs = array();
        foreach($expr->getExpressions() as $innerExpr) {
            $exprs[] = $this->visit($innerExpr);
        }

        switch($expr->getType()) {
        case Term\LogicalExpression::TYPE_AND:
            if(1 >= count($exprs)) {
                return implode(' ', $exprs);
            }
            return Literals::L_AND . Literals::L_COLON . 
                Literals::L_PARENTHESIS_OPEN . 
                implode(' ', $exprs) .
                Literals::L_PARENTHESIS_CLOSE ;
        case Term\LogicalExpression::TYPE_OR:
            if(1 >= count($exprs)) {
                return implode(' ', $exprs);
            }
            return Literals::L_OR . Literals::L_COLON . 
                Literals::L_PARENTHESIS_OPEN . 
                implode(' ', $exprs) .
                Literals::L_PARENTHESIS_CLOSE ;
        case Term\LogicalExpression::TYPE_NOT:
            return Literals::L_NOT . Literals::L_COLON . 
                Literals::L_PARENTHESIS_OPEN . 
                implode(' ', $exprs) .
                Literals::L_PARENTHESIS_CLOSE ;
        default:
            throw new \RuntimeException(sprintf('Unknown type of LogicalExpression operator: [%s]', (string)$expr->getType()));
        }
    }

    public function visitComparisonExpression(Term\ComparisonExpression $expr) 
    {
        $field = $expr->getField();
        $value = $this->visitValueIdentifier($expr->getValue());
        
        switch($expr->getOperator()) {
        case Term\ComparisonExpression::EQ:
            if(null === $value) {
                return $field . Literals::L_COLON . Literals::L_NULL;
            }
            return $field . Literals::L_COLON . Literals::L_EQ . Literals::L_COLON . $value;
        case Term\ComparisonExpression::NEQ:
            if(null === $value) {
                return $field . Literals::L_COLON . Literals::L_ANY;
            }
            return $field . Literals::L_COLON . Literals::L_NE . Literals::L_COLON . $value;
        case Term\ComparisonExpression::GT:
            return $field . Literals::L_COLON . Literals::L_GT . Literals::L_COLON . $value;
        case Term\ComparisonExpression::GTE:
            return $field . Literals::L_COLON . Literals::L_GE . Literals::L_COLON . $value;
        case Term\ComparisonExpression::LT:
            return $field . Literals::L_COLON . Literals::L_LT . Literals::L_COLON . $value;
        case Term\ComparisonExpression::LTE:
            return $field . Literals::L_COLON . Literals::L_LE . Literals::L_COLON . $value;
        default:
            throw new \RuntimeException(sprintf('Unknown Operator[%s] for ComparisonExpression.', (string)$textComparison->getOperator()));
            break;
        }
    }

    public function visitTextComparisonExpression(Term\TextComparisonExpression $textComparison)
    {
        $field = $expr->getField();
        $value = $this->visitValueIdentifier($textComparison->getValue());

        switch($textComparison->getOperator()) {
        case Term\TextComparisonExpression::MATCH:
        case Term\TextComparisonExpression::CONTAIN:
            return $field . Literals::L_COLON . Literals::L_MATCH . Literals::L_COLON . $value;
        case Term\TextComparisonExpression::NOT_MATCH:
        case Term\TextComparisonExpression::NOT_CONTAIN:
            return $field . Literals::L_COLON . Literals::L_NOT_MATCH . Literals::L_COLON . $value;
        default:
            throw new \RuntimeException(sprintf('Unknown Operator[%s] for TextComparisonExpression.', (string)$textComparison->getOperator()));
        }
    }

    public function visitCollectionComparisonExpression(Term\CollectionComparisonExpression $comparison)
    {
        $field = $expr->getField();
        $value = (array)$this->visitValueIdentifier($comparison->getValue());

        switch($comparison->getOperator()) {
        case Term\CollectionComparisonExpression::IN:
            return $field . Literals::L_COLON . Literals::L_IN . Literals::L_COLON . Literals::L_BRANCKET_OPEN . implode(Literals::L_COMMA, $value) . Literals::L_BRANCKET_CLOSE;
        case Term\CollectionComparisonExpression::NOT_IN:
            return $field . Literals::L_COLON . Literals::L_NOT_IN . Literals::L_COLON . Literals::L_BRANCKET_OPEN . implode(Literals::L_COMMA, $value) . Literals::L_BRANCKET_CLOSE;
        default:
            throw new \RuntimeException();
        }
    }

    /**
     * visitField 
     * 
     * @param mixed $field 
     * @access public
     * @return void
     */
    public function visitField($field)
    {
        return $field;
    }

    /**
     * visitValueIdentifier 
     * 
     * @param Term\ValueIdentifier $expr 
     * @access public
     * @return void
     */
    public function visitValueIdentifier(Term\ValueIdentifier $expr)
    {
        return Literals::escape($expr->getValue());
    }
}

