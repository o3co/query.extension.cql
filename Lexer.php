<?php
namespace O3Co\Query\Extension\CQL;

/**
 * Lexer 
 *    
 * @package \O3Co\Query
 * @copyright { COPYRIGHT } (c) { COMPANY }
 * @author Yoshi Aoki <yoshi@44services.jp> 
 * @license MIT
 */
class Lexer 
{
    protected $pos = 0;
    protected $len;
    protected $value;
    //protected $tokens;
    protected $literals;

    /**
     * __construct 
     * 
     * @param mixed $value 
     * @access public
     * @return void
     */
    public function __construct($value, Literals $literals = null)
    {
        $this->value = $value;
        if(!$literals) {
            $literals = new Literals();
        }
        $this->literals = $literals;

        $this->reset();
    }

    public function reset()
    {
        //$this->tokens = array();
        $this->pos = 0;
        $this->len = strlen($this->value);
    }

    public function isEol()
    {
        return $this->len <= $this->pos;
    }

    
    /**
     * match 
     * 
     * @param mixed $token 
     * @access public
     * @return void
     */
    public function match($token, $ignoreSpaces = true)
    {
        switch($token) {
        case Tokens::T_WHITESPACE:
            $pos = $this->getPosAfterSpaces($this->pos);

            $spaces = substr($this->value, $this->pos, $pos - $this->pos);

            $this->pos = $pos;
            return $spaces;
        case Tokens::T_IDENTIFIER:
            if($ignoreSpaces) {
                $this->ignoreSpaces();
            }
            //
            if(preg_match('/^[\w\d_]+/i', substr($this->value, $this->pos), $matches))
            {
                $identifier = $matches[0];
                $this->pos += strlen($identifier);
                return $identifier;
            }

            throw new \InvalidArgumentException(sprintf('T_IDENTIFIER is not matched with next token.'));
            break;
        default:
            $literal = $this->literals->getLiteralForToken($token);
            
            if($this->literals->isSpace($literal)) {
                $ignoreSpaces = false;
            }
            if($ignoreSpaces) {
                $this->ignoreSpaces();
            }
            // If next token is match with the literal, forward the point
            if($literal !== substr($this->value, $this->pos, strlen($literal))) {
                throw new \InvalidArgumentException(sprintf('Token "%s" is not match with next token on "%s"', $literal, substr($this->value, $this->pos)));
            }
            // 
            $this->pos += strlen($literal);

            return $literal;
            break;
        }
    }

    public function ignoreSpaces()
    {
        while($this->pos < $this->len) {
            if(!in_array($this->value[$this->pos], $this->literals->getWhiteSpaces())) {
                break;
            }

            $this->pos++;
        }
    }

    public function getPosAfterSpaces($pos)
    {
        $spaces = $this->literals->getWhiteSpaces();
        while(($pos < $this->len) && in_array($this->value[$pos], $spaces)) {
            $pos++;
        }

        return $pos;
    }

    /**
     * remain 
     * 
     * @access public
     * @return void
     */
    public function remain()
    {
        $remain = substr($this->value, $this->pos);
        // move pos to end.
        $this->pos = $this->len;
        return $remain;
    }

    /**
     * isNextToken 
     *    Check the next token or literal
     * @param integer $token 
     * @access public
     * @return void
     */
    public function isNextToken($token, $ignoreSpaces = true)
    {
        if($this->pos >= $this->len) {
            return Tokens::T_END == $token;
        }
        switch($token) {
        case Tokens::T_END:
            return false;
        case Tokens::T_OPERATOR:
            return 
                    $this->isNextToken(Tokens::T_LOGICAL_OP, $ignoreSpaces) ||
                    $this->isNextToken(Tokens::T_COMPARISON_OP, $ignoreSpaces)
                ;
        case Tokens::T_QUOTE:
            return 
                    $this->isNextToken(Tokens::T_SINGLE_QUOTE, $ignoreSpaces) ||
                    $this->isNextToken(Tokens::T_DOUBLE_QUOTE, $ignoreSpaces)
                ;
        case Tokens::T_LOGICAL_OP:
            return 
                    $this->isNextToken(Tokens::T_AND, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_OR, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_NOT, $ignoreSpaces)
                ;
            break;
        case Tokens::T_COMPARISON_OP:
            return 
                    $this->isNextToken(Tokens::T_EQ, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_NE, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_GT, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_GE, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_LT, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_LE, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_IS_NULL, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_IS_ANY, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_IN, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_RANGE, $ignoreSpaces) || 
                    $this->isNextToken(Tokens::T_MATCH, $ignoreSpaces) 
                ;
            break;
        case Tokens::T_IDENTIFIER:
            $pos = $this->pos;
            if($ignoreSpaces) {
                $pos = $this->getPosAfterSpaces($pos);
            }

            // if alphabet or '_' is following, then identifier.
            return ctype_alpha($this->value[$pos]) || ('_' == $this->value[$pos]);
        default:
            $literal = $this->literals->getLiteralForToken($token);
            if($this->literals->isSpace($literal)) {
                $ignoreSpaces = false;
            }
            return $this->isNextLiteral($literal, $ignoreSpaces);
            break;
        }
    }

    public function isNextLiteral($literal, $ignoreSpaces = true)
    {
        $pos = $this->pos;
        if($ignoreSpaces) {
            $pos = $this->getPosAfterSpaces($pos);
        }
        return $literal == substr($this->value, $pos, strlen($literal));
    }

    /**
     * Get literal.
     *
     * @access public
     * @return literal
     */
    public function getLiterals()
    {
        return $this->literals;
    }
    
    /**
     * Set literal.
     *
     * @access public
     * @param literal the value to set.
     * @return mixed Class instance for method-chanin.
     */
    public function setLiteral(LiteralSet $literals)
    {
        $this->literals = $literals;
        return $this;
    }

    /**
     * createLexer 
     *   Create InternalLexer 
     * @access public
     * @return void
     */
    public function createLexer($value)
    {
       return new self($value, $this->literals);        
    }

    /**
     * until 
     * 
     * @access public
     * @return void
     */
    public function until($tokens)
    {
        $tokens = (array)$tokens;

        if(empty($tokens)) {
            throw new \InvalidArgumentException('$tokens is not specified.');
        }

        $startAt = $this->pos;

        $isEscaped = false;
        $inQuote   = false;
        while($this->pos < $this->len) {
            $isReached = false;

            if(!$isEscaped && $this->isNextToken(Tokens::T_ESCAPE, false)) {
                // if escape symbol is given then flag escape, and go next
                $isEscaped = true;
                $this->pos++;
                continue;
            }

            if(!$inQuote) {
                foreach($tokens as $token) {
                    if($this->isNextToken($token, false)) {
                        $isReached = true;
                        break;
                    }
                }

                if($isReached && !$isEscaped) {
                    break;
                }
            }

            if(!$isEscaped && $this->isNextToken(Tokens::T_QUOTE, false)) {
                $inQuote = !$inQuote;
            }

            $isEscaped = false;
            $this->pos++;
        }

        if($this->pos > $this->len) {
            var_dump($this->value, $this->pos, $this->len, $startAt);
            var_dump($tokens);

            throw new \InvalidArgumentException('EOL reached.');
        }


        return substr($this->value, $startAt, $this->pos - $startAt);
    }

    public function isLiteralAs($literal, $token)
    {
        return $literal == $this->literals->getLiteralForToken($token);
    }
    
    /**
     * Get value.
     *
     * @access public
     * @return value
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Set value.
     *
     * @access public
     * @param value the value to set.
     * @return mixed Class instance for method-chanin.
     */
    public function setValue($value)
    {
        $this->value = $value;

        $this->reset();
        return $this;
    }

    public function guessNextToken(array $tokens)
    {
        $literals = array();
        foreach($tokens as $token) {
            $literals[$token] = $this->literals->getLiteralForToken($token);    
        }

        arsort($literals);

        $nextToken = false;
        foreach($literals as $token => $literal) {
            if($this->isNextLiteral($literal)) {
                $nextToken = $token;
                break;
            }
        }

        return $nextToken;
    }

    public function substr($offset, $len = null)
    {
        if($offset > $this->len) {
            throw new \OutOfRangeException('$offset is over the length');    
        }

        if($this->len < ($offset + $len)) {
            // 
            $len = null;
        }
        return substr($this->value, $offset, $len);
    }

}

