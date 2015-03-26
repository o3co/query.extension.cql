<?php
namespace O3Co\Query\Extension\CQL;

use O3Co\Query\Extension\Http\AbstractParser as AbstractHttpParser; 
use O3Co\Query\Query\Term;
use O3Co\Query\Parser as ParserInterface, 
	O3Co\Query\Fql\Parser as FqlParserInterface
;

/**
 * Parser 
 *    Parse Query string to Simple Expressions.
 * 
 * @uses ParserInterface
 * @uses QueryParser
 * @uses FQLParser
 * @package { PACKAGE }
 * @copyright Copyrights (c) 1o1.co.jp, All Rights Reserved.
 * @author Yoshi<yoshi@1o1.co.jp> 
 * @license { LICENSE }
 */
class Parser extends AbstractHttpParser implements ParserInterface, FQLParserInterface
{
	/**
	 * parseClause 
	 * 
	 * @param mixed $clause 
	 * @access public
	 * @return Clause 
	 */
	public function parseClause($query, $alias)
	{
		$lexer = $this->createLexer($query);
		switch($alias) {
		case 'condition':
			return $this->parseConditionalClause($lexer);
		case 'order':
			return $this->parseOrderClause($lexer);
		case 'limit':
			return $this->parseLimitClause($lexer);
		case 'offset':
			return $this->parseOffsetClause($lexer);
		default:
			throw new UnsupportedException(sprintf('Clause "%s" is not defined on CQL.', $alias));
			break;
		}
	}

    public function parseLimitClause($lexer)
    {
        return new Term\LimitClause($this->parseValueExpresion($lexer));
    }

    public function parseOffsetClause($lexer)
    {
        return new Term\OffsetClause($this->parseValueExpresion($lexer));
    }

	public function parseConditionalClause($lexer)
	{
		$expr = $this->parseExpression($lexer);
		return new Term\ConditionalClause(array($expr));
	}

	protected function parseOrderClause(Lexer $lexer)
	{
		$orders = array();

		do {
			$operator = Term\OrderExpression::ORDER_ASCENDING;
			switch(true) {
			case $lexer->isNextToken(Tokens::T_SORT_ASC):
				$lexer->match(Tokens::T_SORT_ASC);
				$operator = Term\OrderExpression::ORDER_ASCENDING;
				break;
			case $lexer->isNextToken(Tokens::T_SORT_DESC):
				$lexer->match(Tokens::T_SORT_DESC);
				$operator = Term\OrderExpression::ORDER_DESCENDING;
				break;
			default: 
				// ORDER_ASCENDING
				break;
			}
			
			// get target FieldPath  
			if(!$lexer->isNextToken(Tokens::T_IDENTIFIER)) {
				throw new LexerException('Invalid Token for FieldPath.', $lexer);
			}
			$field = $this->parseFieldIdentifier($lexer);
			$orders[]  = new Term\OrderExpression($field, $operator);
		} while(!$lexer->isNextToken(Tokens::T_END));

		return new Term\OrderClause($orders);
	}

	/**
	 * parseFql 
	 * 
	 * @param mixed $query 
	 * @access public
	 * @return BooleanExpression 
	 */
	public function parseFql($field, $query)
	{
		// 
		if(is_string($query)) {
			// 
			$lexer = $this->createLexer((string)$query);
			
			return $this->parseExpression($lexer, $field);
		} else if(is_array($query)) {
			$exprs = array();
			foreach($query as $q) {
				$exprs[] = $this->parseFql($field, $q);
			}

			//return new Expr\AndX($exprs);
			return new Term\LogicalExpression($exprs, Term\LogicalExpression::TYPE_AND);
		} else {
			return new Term\ComparisonExpression($field, $query, Term\ComparisonExpression::EQ);
		}

		throw new \Exception('Invalid Fql query.');
	}

	protected function parseExpression(Lexer $lexer, $field = null, $canGuessOp = true)
	{
		if(!$field) {
			try {
				// try to get identifier field
				if($lexer->isNextToken(Tokens::T_IDENTIFIER)) {
					// try get operator
					$field = $this->parseFieldIdentifier($lexer);
					$lexer->match(Tokens::T_OPERATOR_SEPARATOR);
					
					return $this->parseExpression($lexer, $field, false);
				}
			} catch(\Exception $ex) {
				// reset lexer
				$lexer->reset();

				$field = null;
			}
		}

		if($lexer->isNextToken(Tokens::T_LOGICAL_OP)) {
			//
			return $this->parseLogicalExpression($lexer, $field);
		} else if($lexer->isNextToken(Tokens::T_COMPARISON_OP)) {
			return $this->parseComparisonExpression($lexer, $field);
		} else if($field && $canGuessOp) {
			return new Term\ComparisonExpression($field, $this->parseValueExpression($lexer), Term\ComparisonExpression::EQ);
		}

		throw new \InvalidArgumentException('Field is not specified for Expression.');
	}

	protected function parseLogicalExpression($lexer, $field = null)
	{
		if($lexer->isNextToken(Tokens::T_AND)) {
			$lexer->match(Tokens::T_AND);
			$lexer->match(Tokens::T_OPERATOR_SEPARATOR);
			$exprs = $this->parseCompositeExpression($lexer, $field);
			return new Term\LogicalExpression($exprs, Term\LogicalExpression::TYPE_AND);
			break;
		} else if($lexer->isNextToken(Tokens::T_OR)) {
			$lexer->match(Tokens::T_OR);
			$lexer->match(Tokens::T_OPERATOR_SEPARATOR);
			$exprs = $this->parseCompositeExpression($lexer, $field);
			//return new Expr\OrX($exprs);
			return new Term\LogicalExpression($exprs, Term\LogicalExpression::TYPE_OR);
			break;
		} else if($lexer->isNextToken(Tokens::T_NOT)) {
			$lexer->match(Tokens::T_NOT);
			$lexer->match(Tokens::T_OPERATOR_SEPARATOR);
			$exprs = $this->parseCompositeExpression($lexer, $field);
			if(1 < count($exprs)) {
				throw new \RuntimeException('Logical Expression NOT can only contain 1 expression in.');
			}

			//return new Expr\Not($exprs[0]);
			return new Term\LogicalExpression($exprs, Term\LogicalExpression::TYPE_NOT);
			break;
		} else {
			throw new \InvalidArgumentException('Invalid call or parseLogicalExpression.');
		}
	}

	protected function parseCompositeExpression($lexer, $field = null)
	{
		$lexer->match(Tokens::T_COMPOSITE_BEGIN);

		$exprs = array();
		do {
			$lex = $lexer->until(Tokens::T_COMPOSITE_END, Tokens::T_COMPOSITE_SEPARATOR);
			$exprs[] = $this->parseExpression($this->createLexer($lex, $lexer->getLiterals()), $field);

			if($lexer->isNextToken(Tokens::T_COMPOSITE_SEPARATOR)) {
				$lexer->match(Tokens::T_COMPOSITE_SEPARATOR);
			}
		} while(!$lexer->isNextToken(Tokens::T_COMPOSITE_END));

		$lexer->match(Tokens::T_COMPOSITE_END);

		return $exprs;
	}

	protected function parseComparisonExpression(Lexer $lexer, $field = null)
	{
		if(!$field) {
			throw new \Exception('Field is not specified for comparison expression');
		}
		// get longest match of the operator or false if not
		$operator = $lexer->guessNextToken(array(
				Tokens::T_EQ,
				Tokens::T_NE,
				Tokens::T_GT,
				Tokens::T_GE,
				Tokens::T_LT,
				Tokens::T_LE,
				Tokens::T_MATCH,
				Tokens::T_IS_ANY,
				Tokens::T_IS_NULL,
				Tokens::T_RANGE,
				Tokens::T_IN,
			));

		if(!$operator) {
			throw new \InvalidArgumentException();
		}

		$lexer->match($operator);

		if((Tokens::T_IS_ANY == $operator) || (Tokens::T_IS_NULL == $operator)) {
			return new Term\ComparisonExpression($field, new Term\ValueExpression(null), $this->convertTokenToComparisonOp($operator));
        }
		// parse expression values 
		$lexer->match(Tokens::T_OPERATOR_SEPARATOR);

		if(Tokens::T_IN == $operator) {
			// following should be collectionExpression
			return new Term\CollectionComparisonExpression($field, new Term\ValueExpression($this->parseCollection($lexer)), Term\CollectionComparisonExpression::IN);
		} else if(Tokens::T_RANGE == $operator) {

			// following should be rangeExpression
			return $this->parseRangeExpression($lexer, $field);
		}


		$value = $this->parseValueExpression($lexer);

		switch($operator) {
		case Tokens::T_EQ:
		case Tokens::T_NE:
		case Tokens::T_GT:
		case Tokens::T_GE:
		case Tokens::T_LT:
		case Tokens::T_LE:
			return new Term\ComparisonExpression($field, $value, $this->convertTokenToComparisonOp($operator));
			break;
		case Tokens::T_MATCH:
            // contains wildcard
            if((false !== strpos($value->getValue(), '.')) || (false !== strpos($value->getValue(), '*'))) {
    			return new Term\TextComparisonExpression($field, $value, Term\TextComparisonExpression::MATCH);
            } else {
    			return new Term\TextComparisonExpression($field, $value, Term\TextComparisonExpression::CONTAIN);
            }
			break;
		default:
			throw new \Exception('not comparison');
		}
	}

	protected function parseValueExpression(Lexer $lexer)
	{
		return new Term\ValueExpression($this->parseLiteral($lexer));
	}

    protected function parseLiteral(Lexer $lexer, $until = null)
    {
        if($until) {
            return $lexer->getLiterals()->unescape($lexer->until($until));
        }
        return $lexer->getLiterals()->unescape($lexer->remain());
    }

	protected function parseRangeExpression(Lexer $lexer, $field)
	{
		if($lexer->isNextToken(Tokens::T_RANGE_GT)) {
			$lexer->match(Tokens::T_RANGE_GT);
			$op = Tokens::T_GT;
		} else if($lexer->isNextToken(Tokens::T_RANGE_GE)) {
			$lexer->match(Tokens::T_RANGE_GE);
			$op = Tokens::T_GE;
		} else {
			throw new \Exception('Parser error');
		}

		$value = $this->parseLiteral($lexer, Tokens::T_RANGE_SEPARATOR);
		$min = new Term\ComparisonExpression($field, new Term\ValueExpression($value), $this->convertTokenToComparisonOp($op)); 

		$lexer->match(Tokens::T_RANGE_SEPARATOR);

		$value = $lexer->until(Tokens::T_RANGE_LT, Tokens::T_RANGE_LE);

		if($lexer->isNextToken(Tokens::T_RANGE_LT)) {
			$lexer->match(Tokens::T_RANGE_LT);
			$op = Tokens::T_LT;
		} else if($lexer->isNextToken(Tokens::T_RANGE_LE)) {
			$lexer->match(Tokens::T_RANGE_LE);
			$op = Tokens::T_LE;
		} else {
			throw new \Exception('Parser error');
		}

		$max = new Term\ComparisonExpression($field, new Term\ValueExpression($value), $this->convertTokenToComparisonOp($op));

		return new Term\RangeExpression($field, $min, $max);
	}

	protected function parseCollection(Lexer $lexer)
	{
		$lexer->match(Tokens::T_COLLECTION_BEGIN);

		$values = array();
		while(!$lexer->isNextToken(Tokens::T_COLLECTION_END)) {
			//
			$lex = $lexer->until(Tokens::T_COLLECTION_SEPARATOR, Tokens::T_COLLECTION_END);

			$values[] = $this->parseLiteral($this->createLexer($lex));

			if($lexer->isNextToken(Tokens::T_COLLECTION_SEPARATOR)) {
				$lexer->match(Tokens::T_COLLECTION_SEPARATOR);
			}
		}

		$lexer->match(Tokens::T_COLLECTION_END);

		return $values;
	}

	protected function convertTokenToComparisonOp($token)
	{
		switch($token) {
		case Tokens::T_EQ:
		case Tokens::T_IS_NULL:
			$op = Term\ComparisonExpression::EQ;
			break;
		case Tokens::T_NE:
		case Tokens::T_IS_ANY:
			$op = Term\ComparisonExpression::NEQ;
			break;
		case Tokens::T_GT:
			$op = Term\ComparisonExpression::GT;
			break;
		case Tokens::T_GE:
			$op = Term\ComparisonExpression::GTE;
			break;
		case Tokens::T_LT:
			$op = Term\ComparisonExpression::LT;
			break;
		case Tokens::T_LE:
			$op = Term\ComparisonExpression::LTE;
			break;
		default:
			throw new \Exception('invalid');
		}
		return $op;
	}

	protected function parseFieldIdentifier(Lexer $lexer)
	{
		$domain = array();
		while($lexer->isNextToken(Tokens::T_IDENTIFIER)) {
			$domain[] = $lexer->match(Tokens::T_IDENTIFIER);

			if($lexer->isNextToken(Tokens::T_HIERARCHY_SEPARATOR)) {
				$lexer->match(Tokens::T_HIERARCHY_SEPARATOR);
			}
		}

		return implode('.', $domain);
	}

	public function createLexer($query)
	{
		return new Lexer($query);
	}
}

