<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\VariableEvaluator;
use aieuo\mineflow\variable\parser\VariableLexer;
use aieuo\mineflow\variable\parser\VariableParser;
use aieuo\mineflow\variable\registry\VariableRegistry;
use Ramsey\Uuid\Uuid;
use function preg_match_all;
use function str_replace;

class VariablePlaceholder {

    private readonly string $originalExpression;

    /** @var VariablePlaceholder[] */
    private array $placeholders = [];

    private ?Node $ast = null;

    public function __construct(private string $expression) {
        $this->originalExpression = $this->expression;

        if (preg_match_all("/{((?:[^{}]+|(?R))*)}/u", $this->expression, $matches)) {
            foreach ($matches[1] as $match) {
                $tmp = "tmp_".str_replace("-", "", (string)Uuid::uuid4());
                $this->expression = str_replace("{{$match}}", $tmp, $this->expression);
                $this->placeholders[$tmp] = new VariablePlaceholder($match);
            }
        }
    }

    public function getExpression(): string {
        return $this->expression;
    }

    public function getOriginalExpression(): string {
        return $this->originalExpression;
    }

    public function compile(): void {
        foreach ($this->placeholders as $placeholder) {
            $placeholder->compile();
        }

        $tokens = (new VariableLexer($this->expression))->lexer();
        $this->ast = (new VariableParser($tokens))->parse();
    }

    public function evaluate(array $variables, bool $global = true): Variable {
        if ($this->ast === null) {
            $this->compile();
        }

        $tmpVariables = [];
        foreach ($this->placeholders as $key => $placeholder) {
            $tmpVariables[$key] = $placeholder->evaluate($variables, $global);
        }

        return (new VariableEvaluator(new VariableRegistry($variables), $global, $tmpVariables))->eval($this->ast);
    }
}
