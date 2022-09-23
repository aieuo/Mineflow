<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;

class RecipeVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "recipe";
    }

    public function __construct(Recipe $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $recipe = $this->getRecipe();
        return match ($index) {
            "name" => new StringVariable($recipe->getName()),
            "group" => new StringVariable($recipe->getGroup()),
            "author" => new StringVariable($recipe->getAuthor()),
            "variables" => new MapVariable($recipe->getExecutor()?->getVariables() ?? []),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getRecipe(): Recipe {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "group" => new DummyVariable(DummyVariable::STRING),
            "author" => new DummyVariable(DummyVariable::STRING),
            "variables" => new DummyVariable(DummyVariable::MAP),
        ]);
    }

    public function __toString(): string {
        $recipe = $this->getRecipe();
        return $recipe->getGroup()."/".$recipe->getName();
    }
}
