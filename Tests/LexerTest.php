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

    public function testUntil()
    {
        $lexer = new Lexer('foo and bar "string\"end".');

        $this->assertEquals('foo and bar ', $lexer->until(Tokens::T_DOUBLE_QUOTE));
        $lexer->match(Tokens::T_DOUBLE_QUOTE);
        $this->assertEquals('string\"end', $lexer->until(Tokens::T_DOUBLE_QUOTE));
        $lexer->match(Tokens::T_DOUBLE_QUOTE);

        $lexer = new Lexer('((abc def) (ghi))');
        $this->assertEquals('(', $lexer->match(Tokens::T_COMPOSITE_BEGIN));
        $this->assertEquals('(', $lexer->match(Tokens::T_COMPOSITE_BEGIN));
        $this->assertEquals('abc', $lexer->until(array(Tokens::T_COMPOSITE_END, Tokens::T_COMPOSITE_SEPARATOR)));
        $this->assertEquals(' ', $lexer->match(Tokens::T_COMPOSITE_SEPARATOR));
        $this->assertEquals('def', $lexer->until(array(Tokens::T_COMPOSITE_END, Tokens::T_COMPOSITE_SEPARATOR)));
        $this->assertEquals(')', $lexer->match(Tokens::T_COMPOSITE_END));
        $this->assertEquals(' ', $lexer->match(Tokens::T_COMPOSITE_SEPARATOR));
        $this->assertEquals('(', $lexer->match(Tokens::T_COMPOSITE_BEGIN));
        $this->assertEquals('ghi', $lexer->until(array(Tokens::T_COMPOSITE_END, Tokens::T_COMPOSITE_SEPARATOR)));
        $this->assertEquals(')', $lexer->match(Tokens::T_COMPOSITE_END));
        $this->assertEquals(')', $lexer->match(Tokens::T_COMPOSITE_END));

        $this->assertTrue($lexer->isEol());
    }

}

