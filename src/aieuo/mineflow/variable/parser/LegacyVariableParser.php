<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;


use aieuo\mineflow\variable\parser\node\ConcatenateNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\StringNode;
use aieuo\mineflow\variable\parser\node\ToStringNode;
use aieuo\mineflow\variable\parser\token\VariableToken;
use function count;

class LegacyVariableParser extends EvaluableStringParser {

    // arguments ::= [expression ("," argument)*]
    protected function arguments(): array {
        $arguments = [];
        while (!$this->nextIs(VariableToken::R_PAREN)) {
            if (count($arguments) > 0) {
                $this->consume(VariableToken::COMMA);
            }

            $arguments[] = $this->argument();
            $this->expect(VariableToken::COMMA, VariableToken::R_PAREN);
        }
        return $arguments;
    }

    // argument ::= string? ("{" expression "}" string?)*
    protected function argument(): Node {
        $nodes = [];
        $string = $this->argumentString();
        if ($string !== null) {
            $nodes[] = $string;
        }

        while ($this->nextIs(VariableToken::L_BRACE)) {
            $this->consume(VariableToken::L_BRACE);
            $nodes[] = new ToStringNode($this->expression());
            $this->consume(VariableToken::R_BRACE);

            $string = $this->argumentString();
            if ($string !== null) {
                $nodes[] = $string;
            }
        }

        return count($nodes) === 1 ? $nodes[0] : new ConcatenateNode($nodes);
    }

    protected function argumentString(): ?StringNode {
        $str = "";
        while (!$this->nextIs(VariableToken::COMMA) and !$this->nextIs(VariableToken::R_PAREN) and !$this->nextIs(VariableToken::L_BRACE)) {
            $this->expect();
            if ($this->nextIs(VariableToken::L_PAREN)) {
                $str .= $this->argumentStringParen();
            } else {
                $str .= $this->next();
            }
        }
        return $str === "" ? null : new StringNode($str);
    }

    protected function argumentStringParen(): string {
        $str = $this->consume(VariableToken::L_PAREN);

        while (!$this->nextIs(VariableToken::R_PAREN)) {
            $this->expect();
            if ($this->nextIs(VariableToken::L_PAREN)) {
                $str .= $this->argumentStringParen();
            } else {
                $str .= $this->next();
            }
        }

        $str .= $this->consume(VariableToken::R_PAREN);

        return $str;
    }
}