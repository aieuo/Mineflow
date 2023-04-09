<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipeContainer;

class TriggerHolder {

    /** @var RecipeContainer[][] */
    private array $recipes = [];

    private static ?TriggerHolder $instance = null;

    public static function getInstance(): self {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function createContainer(Trigger $trigger): void {
        if (!isset($this->recipes[$trigger->getType()][$trigger->hash()])) {
            $this->recipes[$trigger->getType()][$trigger->hash()] = new RecipeContainer();
        }
    }

    public function existsRecipe(Trigger $trigger): bool {
        return isset($this->recipes[$trigger->getType()][$trigger->hash()]);
    }

    public function existsRecipeByString(string $type, string $key): bool {
        return isset($this->recipes[$type][$key]);
    }

    public function addRecipe(Trigger $trigger, Recipe $recipe): void {
        $this->createContainer($trigger);
        $this->getRecipes($trigger)?->addRecipe($recipe);
    }

    public function removeRecipe(Trigger $trigger, Recipe $recipe): void {
        $container = $this->recipes[$trigger->getType()][$trigger->hash()];
        $container->removeRecipe($recipe->getPathname());
        if ($container->getRecipeCount() === 0) {
            unset($this->recipes[$trigger->getType()][$trigger->hash()]);
        }
    }

    public function getRecipes(Trigger $trigger): ?RecipeContainer {
        return $this->recipes[$trigger->getType()][$trigger->hash()] ?? null;
    }

    /**
     * @param string $type
     * @return RecipeContainer[]
     */
    public function getRecipesByType(string $type): array {
        return $this->recipes[$type] ?? [];
    }
}
