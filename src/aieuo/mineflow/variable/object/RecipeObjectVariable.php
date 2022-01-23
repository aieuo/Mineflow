<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;

class RecipeObjectVariable extends ObjectVariable {

    public function __construct(Recipe $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $recipe = $this->getRecipe();
        return match ($index) {
            "name" => new StringVariable($recipe->getName()),
            "group" => new StringVariable($recipe->getGroup()),
            "author" => new StringVariable($recipe->getAuthor()),
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
        ]);
    }

    public function __toString(): string {
        $recipe = $this->getRecipe();
        return $recipe->getGroup()."/".$recipe->getName();
    }
}