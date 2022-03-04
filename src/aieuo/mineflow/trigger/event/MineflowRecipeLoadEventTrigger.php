<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\event\MineflowRecipeLoadEvent;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\RecipeObjectVariable;

class MineflowRecipeLoadEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("MineflowRecipeLoadEvent", $subKey, MineflowRecipeLoadEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var MineflowRecipeLoadEvent $event */
        $recipe = $event->getRecipe();
        return [
            "recipe" => new RecipeObjectVariable($recipe),
        ];
    }

    public function getVariablesDummy(): array {
        return [
            "recipe" => new DummyVariable(DummyVariable::RECIPE),
        ];
    }
}