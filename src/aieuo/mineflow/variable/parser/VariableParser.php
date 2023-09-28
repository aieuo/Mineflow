<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\parser\exception\VariableParseException;
use aieuo\mineflow\variable\parser\node\BinaryExpressionNode;
use aieuo\mineflow\variable\parser\node\GlobalMethodNode;
use aieuo\mineflow\variable\parser\node\IdentifierNode;
use aieuo\mineflow\variable\parser\node\MethodNode;
use aieuo\mineflow\variable\parser\node\NameNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\PropertyNode;
use aieuo\mineflow\variable\parser\node\UnaryExpressionNode;
use aieuo\mineflow\variable\parser\node\WrappedNode;
use aieuo\mineflow\variable\parser\token\VariableToken;
use function count;
use function implode;
use function in_array;


class VariableParser {

    private int $pos = 0;
    private int $length;

    /**
     * @param string[] $tokens
     */
    public function __construct(private readonly array $tokens) {
        $this->length = count($this->tokens);
    }

    public function parse(): Node {
        $expression = $this->expression();
        if ($this->valid()) {
            throw new VariableParseException(Language::get("variable.parse.failed.unexpected.end"), $this->tokens, $this->pos);
        }
        return $expression;
    }

    // expression ::= term (("+" | "-") term)*
    private function expression(): Node {
        $left = $this->term();
        while ($this->nextIs(VariableToken::PLUS) or $this->nextIs(VariableToken::MINUS)) {
            $operator = $this->consume(VariableToken::PLUS, VariableToken::MINUS);
            $right = $this->term();
            $left = new BinaryExpressionNode($left, $operator, $right);
        }
        return $left;
    }

    // term ::= unary (("*" | "/") unary)*
    private function term(): Node {
        $left = $this->unary();
        while ($this->nextIs(VariableToken::ASTERISK) or $this->nextIs(VariableToken::SLASH)) {
            $operator = $this->consume(VariableToken::ASTERISK, VariableToken::SLASH);
            $right = $this->unary();
            $left = new BinaryExpressionNode($left, $operator, $right);
        }
        return $left;
    }

    // unary ::= ["+" | "-"] access
    private function unary(): Node {
        if ($this->nextIs(VariableToken::PLUS) or $this->nextIs(VariableToken::MINUS)) {
            $operator = $this->consume(VariableToken::PLUS, VariableToken::MINUS);
            $operand = $this->access();
            return new UnaryExpressionNode($operator, $operand);
        }

        return $this->access();
    }

    // access ::= factor ("(" arguments ")" | "." name ["(" arguments ")"])*
    private function access(): Node {
        $left = $this->factor();
        while ($this->nextIs(VariableToken::DOT) or $this->nextIs(VariableToken::L_PAREN)) {
            if ($this->nextIs(VariableToken::DOT)) {
                $this->consume(VariableToken::DOT);
                $identifier = $this->name();

                if ($this->nextIs(VariableToken::L_PAREN)) {
                    $this->consume(VariableToken::L_PAREN);
                    $arguments = $this->arguments();
                    $this->consume(VariableToken::R_PAREN);
                    $left = new MethodNode($left, $identifier, $arguments);
                } else {
                    $left = new PropertyNode($left, $identifier);
                }
            } else {
                $this->consume(VariableToken::L_PAREN);
                $arguments = $this->arguments();
                $this->consume(VariableToken::R_PAREN);
                $left = new GlobalMethodNode($left, $arguments);
            }
        }
        return $left;
    }

    // factor ::= identifier | "(" expression ")"
    private function factor(): Node {
        if ($this->nextIs(VariableToken::L_PAREN)) {
            $this->consume(VariableToken::L_PAREN);
            $node = new WrappedNode($this->expression());
            $this->consume(VariableToken::R_PAREN);
            return $node;
        }

        return new IdentifierNode($this->consume());
    }

    // name ::= identifier | "(" expression ")"
    private function name(): Node {
        if ($this->nextIs(VariableToken::L_PAREN)) {
            $this->consume(VariableToken::L_PAREN);
            $node = new WrappedNode($this->expression());
            $this->consume(VariableToken::R_PAREN);
            return $node;
        }

        return new NameNode($this->consume());
    }

    // arguments ::= [expression ("," expression)*]
    private function arguments(): array {
        $arguments = [];
        while (!$this->nextIs(VariableToken::R_PAREN)) {
            if (count($arguments) > 0) {
                $this->consume(VariableToken::COMMA);
            }

            $arguments[] = $this->expression();
        }
        return $arguments;
    }

    private function peek(): ?string {
        return $this->tokens[$this->pos] ?? null;
    }

    private function nextIs(string $token): bool {
        return $this->peek() === $token;
    }

    private function consume(string ...$expects): string {
        $token = $this->peek();
        if ($token === null) {
            throw new VariableParseException(Language::get("variable.parse.failed.expected.end", [implode("§e, §c", $expects)]), $this->tokens, $this->pos);
        }
        if ((!empty($expects) and !in_array($token, $expects, true))) {
            throw new VariableParseException(Language::get("variable.parse.failed.expected.token", [implode("§e, §c", $expects), $token]), $this->tokens, $this->pos);
        }

        $this->pos++;
        return $token;
    }

    private function valid(): bool {
        return $this->pos < $this->length;
    }

}
