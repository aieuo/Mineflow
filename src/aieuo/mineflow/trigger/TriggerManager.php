<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\recipe\RecipeContainer;
use aieuo\mineflow\recipe\Recipe;

class TriggerManager {
    /** @var RecipeContainer[]*/
    protected $recipes = [];

    /**
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool {
        return isset($this->recipes[$key]);
    }

    public function add(string $key, Recipe $recipe): void {
        $container = $this->get($key) ?? new RecipeContainer();
        $container->addRecipe($recipe);
    }

    /**
     * @param string $key
     * @return RecipeContainer|null
     */
    public function get(string $key): ?RecipeContainer {
        return $this->recipes[$key] ?? null;
    }

    /**
     * @return RecipeContainer[]
     */
    public function getAll(): array {
        return $this->recipes;
    }

    /**
     * @param string $key
     * @return void
     */
    public function remove(string $key): void {
        unset($this->recipes[$key]);
    }

    public function removeRecipe(string $key, string $name): void {
        if (!$this->exists($key)) return;
        $container = $this->get($key);
        $container->remove($name);
    }
}