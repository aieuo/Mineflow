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
use aieuo\mineflow\variable\parser\node\StringNode;
use aieuo\mineflow\variable\parser\node\UnaryExpressionNode;
use aieuo\mineflow\variable\parser\node\WrappedNode;
use aieuo\mineflow\variable\parser\token\VariableToken;
use function count;
use function implode;
use function in_array;


class VariableParser {

    /** @var VariableToken[] */
    protected array $tokens = [];
    protected int $pos = 0;
    protected int $length;

    /**
     * @param VariableToken[] $tokens
     * @return Node
     */
    final public function parse(array $tokens): Node {
        $this->tokens = $tokens;
        $this->length = count($tokens);

        if ($this->length === 0) {
            return new StringNode("");
        }

        $expression = $this->doParse();
        if ($this->valid()) {
            throw new VariableParseException(Language::get("variable.parse.failed.unexpected.end"), $this->tokens, $this->pos);
        }
        return $expression;
    }

    protected function doParse(): Node {
        return $this->expression();
    }

    // expression ::= term (("+" | "-") term)*
    protected function expression(): Node {
        $left = $this->term();
        while ($this->nextIs(VariableToken::PLUS) or $this->nextIs(VariableToken::MINUS)) {
            $operator = $this->consume(VariableToken::PLUS, VariableToken::MINUS);
            $right = $this->term();
            $left = new BinaryExpressionNode($left, $operator, $right);
        }
        return $left;
    }

    // term ::= unary (("*" | "/") unary)*
    protected function term(): Node {
        $left = $this->unary();
        while ($this->nextIs(VariableToken::ASTERISK) or $this->nextIs(VariableToken::SLASH)) {
            $operator = $this->consume(VariableToken::ASTERISK, VariableToken::SLASH);
            $right = $this->unary();
            $left = new BinaryExpressionNode($left, $operator, $right);
        }
        return $left;
    }

    // unary ::= ["+" | "-"] access
    protected function unary(): Node {
        if ($this->nextIs(VariableToken::PLUS) or $this->nextIs(VariableToken::MINUS)) {
            $operator = $this->consume(VariableToken::PLUS, VariableToken::MINUS);
            $operand = $this->access();
            return new UnaryExpressionNode($operator, $operand);
        }

        return $this->access();
    }

    // access ::= factor ("(" arguments ")" | "." name ["(" arguments ")"])*
    protected function access(): Node {
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
    protected function factor(): Node {
        $this->expect(VariableToken::STRING, VariableToken::L_PAREN);

        $node = $this->brackets();
        if ($node !== null) return $node;

        $token = $this->consumeToken(VariableToken::STRING);
        return new IdentifierNode($token->getToken(), $token->getTrimmedLeft(), $token->getTrimmedRight());
    }

    // name ::= identifier | "(" expression ")"
    protected function name(): Node {
        $this->expect(VariableToken::STRING, VariableToken::L_PAREN);
        
        $node = $this->brackets();
        if ($node !== null) return $node;

        $token = $this->consumeToken(VariableToken::STRING);
        return new NameNode($token->getToken(), $token->getTrimmedLeft(), $token->getTrimmedRight());
    }

    // "(" expression ")"
    protected function brackets(): ?Node {
        if ($this->nextIs(VariableToken::L_PAREN)) {
            $this->consume(VariableToken::L_PAREN);
            $node = new WrappedNode($this->expression());
            $this->consume(VariableToken::R_PAREN);
            return $node;
        }

        return null;
    }

    // arguments ::= [expression ("," expression)*]
    protected function arguments(): array {
        $arguments = [];
        while (!$this->nextIs(VariableToken::R_PAREN)) {
            if (count($arguments) > 0) {
                $this->consume(VariableToken::COMMA);
            }

            $arguments[] = $this->expression();
            $this->expect(VariableToken::COMMA, VariableToken::R_PAREN);
        }
        return $arguments;
    }

    protected function peek(): ?VariableToken {
        return $this->tokens[$this->pos] ?? null;
    }

    protected function next(): ?VariableToken {
        return $this->tokens[$this->pos ++] ?? null;
    }

    protected function nextIs(string $type): bool {
        return $this->peek()?->getType() === $type;
    }

    protected function expect(string ...$expects): VariableToken {
        $token = $this->peek();
        if ($token === null) {
            throw new VariableParseException(Language::get("variable.parse.failed.expected.end", [implode("§e, §7", $expects)]), $this->tokens, $this->pos);
        }
        if ((!empty($expects) and !in_array($token->getType(), $expects, true))) {
            throw new VariableParseException(Language::get("variable.parse.failed.expected.token", [implode("§e, §7", $expects), $token]), $this->tokens, $this->pos);
        }
        return $token;
    }

    protected function consume(string ...$expects): string {
        $token = $this->expect(...$expects);

        $this->pos++;
        return $token->getToken();
    }

    protected function consumeToken(string ...$expects): VariableToken {
        $token = $this->expect(...$expects);

        $this->pos++;
        return $token;
    }

    protected function valid(): bool {
        return $this->pos < $this->length;
    }

}