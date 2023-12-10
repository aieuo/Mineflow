<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

use function preg_match_all;

class VariableString {


    /** @var VariablePlaceholder[]  */
    private array $placeholders = [];

    public function __construct(private readonly string $raw) {
        if (preg_match_all("/{((?:[^{}]+|(?R))*)}/u", $raw, $matches)) {
            foreach ($matches[1] as $match) {
                $this->placeholders[$match] = new VariablePlaceholder($match);
            }
        }
    }

    public function getRaw(): string {
        return $this->raw;
    }

    public function compile(): void {
        foreach ($this->placeholders as $placeholder) {
            $placeholder->compile();
        }
    }

    public function get(array $variables, bool $global = true): string {
        $result = $this->raw;
        foreach ($this->placeholders as $key => $placeholder) {
            $variable = $placeholder->evaluate($variables, $global);
            $result = str_replace("{{$key}}", (string)$variable, $result);
        }

        return $result;
    }

    public function __toString(): string {
        return $this->raw;
    }
}
