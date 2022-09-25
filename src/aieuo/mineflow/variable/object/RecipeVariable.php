<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;

class RecipeVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "recipe";
    }

    public function __construct(private Recipe $recipe) {
    }

    public function getValue(): Recipe {
        return $this->recipe;
    }

    public function __toString(): string {
        $recipe = $this->getValue();
        return $recipe->getGroup()."/".$recipe->getName();
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty(
            $class, "name", new DummyVariable(StringVariable::class),
            fn(Recipe $recipe) => new StringVariable($recipe->getName()),
        );
        self::registerProperty(
            $class, "group", new DummyVariable(StringVariable::class),
            fn(Recipe $recipe) => new StringVariable($recipe->getGroup()),
        );
        self::registerProperty(
            $class, "author", new DummyVariable(StringVariable::class),
            fn(Recipe $recipe) => new StringVariable($recipe->getAuthor()),
        );
        self::registerProperty(
            $class, "variables", new DummyVariable(MapVariable::class),
            fn(Recipe $recipe) => new MapVariable($recipe->getExecutor()?->getVariables() ?? []),
        );
    }
}
