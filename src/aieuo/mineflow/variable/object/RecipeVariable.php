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

    public function __construct(private Recipe $recipe) {
    }

    public function getValue(): Recipe {
        return $this->recipe;
    }

    public function getValueFromIndex(string $index): ?Variable {
        $recipe = $this->getValue();
        return match ($index) {
            "name" => new StringVariable($recipe->getName()),
            "group" => new StringVariable($recipe->getGroup()),
            "author" => new StringVariable($recipe->getAuthor()),
            "variables" => new MapVariable($recipe->getExecutor()?->getVariables() ?? []),
            default => parent::getValueFromIndex($index),
        };
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
            "group" => new DummyVariable(StringVariable::class),
            "author" => new DummyVariable(StringVariable::class),
            "variables" => new DummyVariable(MapVariable::class),
        ]);
    }

    public function __toString(): string {
        $recipe = $this->getValue();
        return $recipe->getGroup()."/".$recipe->getName();
    }
}
