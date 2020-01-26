<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipeContainer;

class TriggerHolder {

    /** @var RecipeContainer[][] */
    private $recipes = [];

    /** @var TriggerHolder */
    private static $instance = null;

    public static function getInstance(): self {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function addTrigger(Trigger $trigger) {
        if (!isset($this->recipes[$trigger->getType()][$trigger->getKey()]))  {
            $this->recipes[$trigger->getType()][$trigger->getKey()] = new RecipeContainer();
        }
    }

    public function existsRecipeByTrigger(Trigger $trigger): bool {
        return isset($this->recipes[$trigger->getType()][$trigger->getKey()]);
    }

    public function existsRecipe(string $type, string $key): bool {
        return isset($this->recipes[$type][$key]);
    }

    public function addRecipe(Trigger $trigger, Recipe $recipe): void {
        $this->addTrigger($trigger);
        $container = $this->getRecipes($trigger);
        $container->addRecipe($recipe);
    }

    public function removeRecipe(Trigger $trigger, Recipe $recipe): void {
        $container = $this->recipes[$trigger->getType()][$trigger->getKey()];
        $container->removeRecipe($recipe->getName());
        if ($container->getRecipeCount() === 0) {
            unset($this->recipes[$trigger->getType()][$trigger->getKey()]);
        }
    }

    public function getRecipes(Trigger $trigger): ?RecipeContainer {
        return $this->recipes[$trigger->getType()][$trigger->getKey()] ?? null;
    }
}