<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipeContainer;

class TriggerHolder {

    /** @var RecipeContainer[][][] */
    private array $recipes = [];

    private static ?TriggerHolder $instance = null;

    public static function getInstance(): self {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function createContainer(Trigger $trigger): void {
        if (!isset($this->recipes[$trigger->getType()][$trigger->getKey()][$trigger->getSubKey()])) {
            $this->recipes[$trigger->getType()][$trigger->getKey()][$trigger->getSubKey()] = new RecipeContainer();
        }
    }

    public function existsRecipe(Trigger $trigger): bool {
        return isset($this->recipes[$trigger->getType()][$trigger->getKey()][$trigger->getSubKey()]);
    }

    public function existsRecipeByString(string $type, string $key, string $subKey = ""): bool {
        return isset($this->recipes[$type][$key][$subKey]);
    }

    public function addRecipe(Trigger $trigger, Recipe $recipe): void {
        $this->createContainer($trigger);
        $container = $this->getRecipes($trigger);
        $container->addRecipe($recipe);
    }

    public function removeRecipe(Trigger $trigger, Recipe $recipe): void {
        $container = $this->recipes[$trigger->getType()][$trigger->getKey()][$trigger->getSubKey()];
        $container->removeRecipe($recipe->getPathname());
        if ($container->getRecipeCount() === 0) {
            unset($this->recipes[$trigger->getType()][$trigger->getKey()][$trigger->getSubKey()]);
        }
    }

    public function getRecipes(Trigger $trigger): ?RecipeContainer {
        return $this->recipes[$trigger->getType()][$trigger->getKey()][$trigger->getSubKey()] ?? null;
    }

    /**
     * @param Trigger $trigger
     * @return RecipeContainer[]
     */
    public function getRecipesWithSubKey(Trigger $trigger): array {
        return $this->recipes[$trigger->getType()][$trigger->getKey()] ?? [];
    }
}