<?php
namespace O3Co\Query\Extension\CQL\Tests;

use O3Co\Query\Extension\CQL\Lexer;
use O3Co\Query\Extension\CQL\Tokens;

class LexerTest extends \PHPUnit_Framework_TestCase 
{
	public function testSuccess()
	{
		$lexer = new Lexer('foo and');
		$this->assertEquals('foo', $lexer->match(Tokens::T_IDENTIFIER));
		$this->assertEquals('and', $lexer->match(Tokens::T_AND));
		$this->assertTrue($lexer->isEol());
		$lexer->reset();

		$this->assertTrue($lexer->isNextToken(Tokens::T_IDENTIFIER));
		$lexer->match(Tokens::T_IDENTIFIER);
		$this->assertEquals(' ', $lexer->match(Tokens::T_WHITESPACE, false));

	}

}

