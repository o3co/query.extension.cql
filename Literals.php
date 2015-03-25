<?php
namespace O3Co\Query\Extension\CQL;

/**
 * Literals 
 * 
 * @package { PACKAGE }
 * @copyright Copyrights (c) 1o1.co.jp, All Rights Reserved.
 * @author Yoshi<yoshi@1o1.co.jp> 
 * @license { LICENSE }
 */
class Literals 
{
	const L_PLUS   = '+';
	const L_MINUS  = '-';
	const L_EQ     = '=';
	const L_NE     = '!=';
	const L_GT     = '>';
	const L_GE     = '>=';
	const L_LT     = '<';
	const L_LE     = '<=';
	const L_MATCH  = '%';
	const L_NOT_MATCH = '!%';
	const L_OR     = 'or';
	const L_AND    = 'and';
	const L_NOT    = 'not';
	const L_RANGE  = 'range';
	const L_IN     = 'in';
	const L_NOT_IN = '!in';
	
	const L_BRANCKET_OPEN  = '[';
	const L_BRANCKET_CLOSE = ']';

	const L_PARENTHESIS_OPEN  = '(';
	const L_PARENTHESIS_CLOSE = ')';

	const L_CURLY_BRACE_OPEN   = '{';
	const L_CURLY_BRACE_CLOSE  = '}';

	const L_COMMA   = ',';
	const L_COLON   = ':';
	const L_DOT     = '.';
	const L_EXCLAMATION = '!';

	const L_NULL    = '~';
	const L_ANY     = '*';

	const L_ESCAPE  = "\\";
	const L_SPACE   = ' ';
	const L_TAB     = "\t";

	const L_SINGLE_QUOTE = "'";
	const L_DOUBLE_QUOTE = "\"";

	
	static public function decode($value)
	{
		return urlencode($value);
	}
	
	static public function encode($value)
	{
		return urlencode($value);
	}

	static public function unescape($value)
	{
        if(is_string($value)) {
    		return trim(stripcslashes($value), " \t\n\r\0\x0B\"'");
        } else if(is_array($value)) {
            foreach($value as $k => $v) {
                $value[$k] = self::unescape($v);
            }
        }
        return $value;
	}

	static public function escape($value)
	{
        if(is_string($value)) {
		    $value = addslashes($value);
            
            // if include spaces, then quote the value
            if(is_string($value) && preg_match('/\s/ui', $value)) {
                return self::L_DOUBLE_QUOTE . $value . self::L_DOUBLE_QUOTE; 
            }
        } else if(is_array($value)) {
            foreach($value as $k => $v) {
                $value[$k] = self::secape($v);
            }
        }
        return $value;
	}

	public function trimValue($value)
	{
		return rtrim($value, self::L_SPACE . self::L_TAB);
	}

	public function getWhiteSpaces()
	{
		return array(
				self::L_SPACE,
				self::L_TAB
			);
	}

	public function isSpace($char)
	{
		return ($char == self::L_SPACE) || ($char == self::L_TAB); 
	}

	public function getLiteralForToken($token, array $options = array())
	{
		switch($token) {
		case Tokens::T_OPERATOR_SEPARATOR:
			return self::L_COLON;
		case Tokens::T_OR:
			return self::L_OR;
		case Tokens::T_AND:
			return self::L_AND;
		case Tokens::T_NOT:
			return self::L_NOT;
		case Tokens::T_EQ:
			return self::L_EQ;
		case Tokens::T_NE:
			return self::L_NE;
		case Tokens::T_GT:
			return self::L_GT;
		case Tokens::T_GE:
			return self::L_GE;
		case Tokens::T_LT:
			return self::L_LT;
		case Tokens::T_LE:
			return self::L_LE;
		case Tokens::T_MATCH:
			return self::L_MATCH;
		case Tokens::T_IS_NULL:
			return self::L_NULL;
		case Tokens::T_IS_ANY:
			return self::L_ANY;
		case Tokens::T_COLLECTION_BEGIN:
			return self::L_BRANCKET_OPEN;
		case Tokens::T_COLLECTION_END:
			return self::L_BRANCKET_CLOSE;
		case Tokens::T_COLLECTION_SEPARATOR:
			return self::L_COMMA;
		case Tokens::T_COMPOSITE_BEGIN:
			return self::L_PARENTHESIS_OPEN;
		case Tokens::T_COMPOSITE_END:
			return self::L_PARENTHESIS_CLOSE;
		case Tokens::T_COMPOSITE_SEPARATOR:
			return self::L_SPACE;
			//return self::L_COMMA;
		case Tokens::T_SINGLE_QUOTE:
			return self::L_SINGLE_QUOTE;
		case Tokens::T_DOUBLE_QUOTE:
			return self::L_DOUBLE_QUOTE;
		case Tokens::T_ESCAPE:
			return self::L_ESCAPE;
		case Tokens::T_HIERARCHY_SEPARATOR:
			return self::L_DOT;

		case Tokens::T_RANGE:
			return self::L_RANGE;
		case Tokens::T_RANGE_GT:
			return self::L_CURLY_BRACE_OPEN;
		case Tokens::T_RANGE_GE:
			return self::L_BRANCKET_OPEN;
		case Tokens::T_RANGE_LT:
			return self::L_CURLY_BRACE_CLOSE;
		case Tokens::T_RANGE_LE:
			return self::L_BRANCKET_CLOSE;
		case Tokens::T_RANGE_SEPARATOR:
			return self::L_COMMA;
		case Tokens::T_IN:
			return self::L_IN;

		case Tokens::T_SORT_ASC:
			return self::L_PLUS;
		case Tokens::T_SORT_DESC:
			return self::L_MINUS;
		default:
			break;
		}
		throw new \Exception(sprintf('Unsupported token [%d] for literals.', $token));
	}

	public function getLiteralsForTokens(array $tokens) 
	{
		$literals = array();
		foreach($tokens as $token) {
			$literals[$token] = $this->getLiteralForToken($token);
		}
		return $literals;
	}
}

