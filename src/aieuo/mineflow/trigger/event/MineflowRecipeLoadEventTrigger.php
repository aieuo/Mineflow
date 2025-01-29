<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\event\MineflowRecipeLoadEvent;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\RecipeVariable;

class MineflowRecipeLoadEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("MineflowRecipeLoadEvent", MineflowRecipeLoadEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var MineflowRecipeLoadEvent $event */
        $recipe = $event->getRecipe();
        return [
            "recipe" => new RecipeVariable($recipe),
        ];
    }

    public function getVariablesDummy(): array {
        return [
            "recipe" => new DummyVariable(RecipeVariable::class),
        ];
    }
}