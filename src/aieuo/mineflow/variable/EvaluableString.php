<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\parser\EvaluableStringLexer;
use aieuo\mineflow\variable\parser\EvaluableStringParser;
use aieuo\mineflow\variable\parser\node\ConcatenateNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\StringNode;
use aieuo\mineflow\variable\parser\VariableEvaluator;
use aieuo\mineflow\variable\registry\VariableRegistry;
use function count;
use function str_contains;

class EvaluableString {

    private bool $compiled = false;

    private ?Node $ast = null;

    private ?string $text = null;

    public function __construct(private readonly string $raw) {
    }

    public function getRaw(): string {
        return $this->raw;
    }

    public function getAst(): ?Node {
        if (!$this->compiled) {
            $this->compile();
        }

        return $this->ast;
    }

    public function isSimpleText(): bool {
        if (!$this->compiled) {
            $this->compile();
        }

        return $this->text !== null;
    }

    public function compile(): void {
        $this->compiled = true;

        if (!str_contains($this->raw, "{") or !str_contains($this->raw, "}")) {
            $this->text = $this->raw;
            return;
        }

        $tokens = (new EvaluableStringLexer())->lexer($this->raw);
        $ast = (new EvaluableStringParser())->parse($tokens);
        $this->ast = $ast;

        if ($ast instanceof StringNode) {
            $this->text = $ast->getString();
        }
        if ($ast instanceof ConcatenateNode) {
            if (count($ast->getNodes()) === 0) {
                $this->text = "";
            }
            if (count($ast->getNodes()) === 1) {
                $node = $ast->getNodes()[0];
                if ($node instanceof StringNode) {
                    $this->text = $node->getString();
                }
            }
        }
    }

    public function eval(VariableRegistry $registry, bool $global = true): string {
        if (!$this->compiled) {
            $this->compile();
        }

        return $this->text ?? (string)(new VariableEvaluator($registry, $global))->eval($this->ast);
    }

    public function __toString(): string {
        return $this->raw;
    }
}