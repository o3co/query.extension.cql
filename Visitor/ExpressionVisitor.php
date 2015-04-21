<?php
namespace O3Co\Query\Extension\CQL\Visitor;

use O3Co\Query\Extension\Http\Visitor\ExpressionVisitor as BaseVisitor;
use O3Co\Query\Extension\CQL\Literals;
use O3Co\Query\Query\Expr;

/**
 * ExpressionVisitor 
 *   CQL ExpressionVisitor is to generate Http based CQL native query from SimpleExpression. 
 * @uses BaseVisitor
 * @package \O3Co\Query
 * @copyright Copyrights (c) 1o1.co.jp, All Rights Reserved.
 * @author Yoshi<yoshi@1o1.co.jp> 
 * @license MIT
 */
class ExpressionVisitor extends BaseVisitor
{
    public function visitStatement(Expr\Statement $statement)
    {
        $this->reset();

        // apply condition 
        if($statement->hasClause('condition')) {
            $this->queryComponents['query'] = $this->visitConditionalClause($statement->getClause('condition'));
        }

        // apply order
        if($statement->hasClause('order')) {
            $this->queryComponents['order'] = $this->visitOrderClause($statement->getClause('order'));
        }
    }

    public function visitConditionalClause(Expr\ConditionalClause $clause)
    {
        return $clause->getExpression()->dispatch($this);
    }

    public function visitOrderClause(Expr\OrderClause $clause)
    {
        $exprs = array();
        foreach($clause->getExpressions() as $expr) {
            $exprs[] = $this->visitOrderExpression($expr);
        }
        return implode(',', $exprs);
    }

    public function visitOrderExpression(Expr\OrderExpression$expr)
    {
        if($expr->isAsc()) {
            return Literals::L_PLUS . $this->visitField($expr->getField());
        } else {
            return Literals::L_MINUS . $this->visitField($expr->getField());
        }
    }
    
    /**
     * visitLogicalExpression 
     * 
     * @param Expr\LogicalExpression $expr 
     * @access public
     * @return void
     */
    public function visitLogicalExpression(Expr\LogicalExpression $expr)
    {
        $exprs = array();
        foreach($expr->getExpressions() as $innerExpr) {
            $exprs[] = $this->visit($innerExpr);
        }

        switch($expr->getType()) {
        case Expr\LogicalExpression::TYPE_AND:
            if(1 >= count($exprs)) {
                return implode(' ', $exprs);
            }
            return Literals::L_AND . Literals::L_COLON . 
                Literals::L_PARENTHESIS_OPEN . 
                implode(' ', $exprs) .
                Literals::L_PARENTHESIS_CLOSE ;
        case Expr\LogicalExpression::TYPE_OR:
            if(1 >= count($exprs)) {
                return implode(' ', $exprs);
            }
            return Literals::L_OR . Literals::L_COLON . 
                Literals::L_PARENTHESIS_OPEN . 
                implode(' ', $exprs) .
                Literals::L_PARENTHESIS_CLOSE ;
        case Expr\LogicalExpression::TYPE_NOT:
            return Literals::L_NOT . Literals::L_COLON . 
                Literals::L_PARENTHESIS_OPEN . 
                implode(' ', $exprs) .
                Literals::L_PARENTHESIS_CLOSE ;
        default:
            throw new \RuntimeException(sprintf('Unknown type of LogicalExpression operator: [%s]', (string)$expr->getType()));
        }
    }

    public function visitComparisonExpression(Expr\ComparisonExpression $expr) 
    {
        $field = $expr->getField();
        $value = $this->visitValueIdentifier($expr->getValue());
        
        switch($expr->getOperator()) {
        case Expr\ComparisonExpression::EQ:
            if(null === $value) {
                return $field . Literals::L_COLON . Literals::L_NULL;
            }
            return $field . Literals::L_COLON . Literals::L_EQ . Literals::L_COLON . $value;
        case Expr\ComparisonExpression::NEQ:
            if(null === $value) {
                return $field . Literals::L_COLON . Literals::L_ANY;
            }
            return $field . Literals::L_COLON . Literals::L_NE . Literals::L_COLON . $value;
        case Expr\ComparisonExpression::GT:
            return $field . Literals::L_COLON . Literals::L_GT . Literals::L_COLON . $value;
        case Expr\ComparisonExpression::GTE:
            return $field . Literals::L_COLON . Literals::L_GE . Literals::L_COLON . $value;
        case Expr\ComparisonExpression::LT:
            return $field . Literals::L_COLON . Literals::L_LT . Literals::L_COLON . $value;
        case Expr\ComparisonExpression::LTE:
            return $field . Literals::L_COLON . Literals::L_LE . Literals::L_COLON . $value;
        default:
            throw new \RuntimeException(sprintf('Unknown Operator[%s] for ComparisonExpression.', (string)$textComparison->getOperator()));
            break;
        }
    }

    public function visitTextComparisonExpression(Expr\TextComparisonExpression $textComparison)
    {
        $field = $expr->getField();
        $value = $this->visitValueIdentifier($textComparison->getValue());

        switch($textComparison->getOperator()) {
        case Expr\TextComparisonExpression::MATCH:
        case Expr\TextComparisonExpression::CONTAIN:
            return $field . Literals::L_COLON . Literals::L_MATCH . Literals::L_COLON . $value;
        case Expr\TextComparisonExpression::NOT_MATCH:
        case Expr\TextComparisonExpression::NOT_CONTAIN:
            return $field . Literals::L_COLON . Literals::L_NOT_MATCH . Literals::L_COLON . $value;
        default:
            throw new \RuntimeException(sprintf('Unknown Operator[%s] for TextComparisonExpression.', (string)$textComparison->getOperator()));
        }
    }

    public function visitCollectionComparisonExpression(Expr\CollectionComparisonExpression $comparison)
    {
        $field = $expr->getField();
        $value = (array)$this->visitValueIdentifier($comparison->getValue());

        switch($comparison->getOperator()) {
        case Expr\CollectionComparisonExpression::IN:
            return $field . Literals::L_COLON . Literals::L_IN . Literals::L_COLON . Literals::L_BRANCKET_OPEN . implode(Literals::L_COMMA, $value) . Literals::L_BRANCKET_CLOSE;
        case Expr\CollectionComparisonExpression::NOT_IN:
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
     * @param Expr\ValueIdentifier $expr 
     * @access public
     * @return void
     */
    public function visitValueIdentifier(Expr\ValueIdentifier $expr)
    {
        return Literals::escape($expr->getValue());
    }
}

