<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;

use aieuo\mineflow\variable\parser\node\ConcatenateNode;
use aieuo\mineflow\variable\parser\node\EvaluableIdentifierNode;
use aieuo\mineflow\variable\parser\node\EvaluableNameNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\StringNode;
use aieuo\mineflow\variable\parser\node\ToStringNode;
use aieuo\mineflow\variable\parser\token\VariableToken;


class EvaluableStringParser extends VariableParser {

    protected function doParse(): ConcatenateNode {
        return $this->strings();
    }

    // strings ::= string? ({expression} string?)*
    protected function strings(): ConcatenateNode {
        $nodes = [];

        if (!$this->nextIs(VariableToken::L_BRACE)) {
            $str = $this->consumeToken();
            if ($str->getToken() !== "") {
                $nodes[] = new StringNode($str->getTrimmedLeft().$str->getToken().$str->getTrimmedRight());
            }
        }

        while ($this->valid() and $this->nextIs(VariableToken::L_BRACE)) {
            $this->consumeToken(VariableToken::L_BRACE);
            $nodes[] = new ToStringNode($this->expression());
            $this->consumeToken(VariableToken::R_BRACE);

            if ($this->valid() and !$this->nextIs(VariableToken::L_BRACE)) {
                $str = $this->consumeToken();
                if ($str->getToken() !== "") {
                    $nodes[] = new StringNode($str->getTrimmedLeft().$str->getToken().$str->getTrimmedRight());
                }
            }
        }
        return new ConcatenateNode($nodes);
    }

    // factor ::= identifier_string | "(" expression ")"
    protected function factor(): Node {
        $this->expect(VariableToken::STRING, VariableToken::L_PAREN, VariableToken::L_BRACE);

        $node = $this->brackets();
        if ($node !== null) return $node;

        return new EvaluableIdentifierNode($this->identifierString());
    }

    // name ::= identifier_string | "(" expression ")"
    protected function name(): Node {
        $this->expect(VariableToken::STRING, VariableToken::L_PAREN, VariableToken::L_BRACE);

        $node = $this->brackets();
        if ($node !== null) return $node;

        return new EvaluableNameNode($this->identifierString());
    }

    // identifier_string ::= string? ("{" expression "}" string?)*
    protected function identifierString(): Node {
        $this->expect(VariableToken::STRING, VariableToken::L_BRACE);

        $nodes = [];
        if ($this->nextIs(VariableToken::STRING)) {
            $token = $this->consumeToken(VariableToken::STRING);
            $nodes[] = new StringNode($token->getToken(), $token->getTrimmedLeft(), $token->getTrimmedRight());
        }

        while ($this->nextIs(VariableToken::L_BRACE)) {
            $this->consume(VariableToken::L_BRACE);
            $nodes[] = new ToStringNode($this->expression());
            $this->consume(VariableToken::R_BRACE);

            if ($this->nextIs(VariableToken::STRING)) {
                $token = $this->consumeToken(VariableToken::STRING);
                $nodes[] = new StringNode($token->getToken(), $token->getTrimmedLeft(), $token->getTrimmedRight());
            }
        }

        return count($nodes) === 1 ? $nodes[0] : new ConcatenateNode($nodes);
    }
}