<?php

namespace aieuo\mineflow\recipe;

use pocketmine\entity\Entity;
use pocketmine\event\Event;

class RecipeContainer {

    /** @var Recipe[] */
    private array $recipes;

    protected bool $changed = false;

    public function __construct(array $recipes = []) {
        $this->recipes = $recipes;
    }

    public function addRecipe(Recipe $recipe): void {
        $this->recipes[$recipe->getPathname()] = $recipe;
        $this->changed = true;
    }

    public function getRecipe(string $key): ?Recipe {
        return $this->recipes[$key] ?? null;
    }

    /**
     * @return Recipe[]
     */
    public function getAllRecipe(): array {
        return $this->recipes;
    }

    public function removeRecipe(string $key): void {
        unset($this->recipes[$key]);
    }

    public function existsRecipe(string $key): bool {
        return isset($this->recipes[$key]);
    }

    public function getRecipeCount(): int {
        return count($this->getAllRecipe());
    }

    public function executeAll(Entity $target = null, array $variables = [], ?Event $event = null): void {
        foreach ($this->getAllRecipe() as $recipe) {
            if ($recipe->isEnabled()) {
                $recipe->executeAllTargets($target, $variables, $event);
            }
        }
    }
}
