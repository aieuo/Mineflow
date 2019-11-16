<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\recipe\RecipeContainer;
use aieuo\mineflow\recipe\Recipe;

class TriggerManager {

    const TRIGGER_BLOCK = "block";
    const TRIGGER_EVENT = "event";

    /** @var RecipeContainer[]*/
    protected $recipes = [];

    /** @var TriggerManager */
    private static $managers = [];

    public static function init(): void {
        self::$managers = [
            self::TRIGGER_BLOCK => new BlockTriggerManager,
            self::TRIGGER_EVENT => new EventTriggerManager,
        ];
    }

    public static function getManager(string $type): ?TriggerManager {
        return self::$managers[$type] ?? null;
    }

    public function exists(string $key): bool {
        return isset($this->recipes[$key]);
    }

    public function existsRecipe(string $key, string $name) {
        if (!$this->exists($key)) return false;
        return $this->get($key)->existsRecipe($name);
    }

    public function add(string $key, Recipe $recipe): void {
        if (!$this->exists($key)) $this->recipes[$key] = new RecipeContainer;
        $container = $this->get($key);
        $container->addRecipe($recipe);
    }

    public function get(string $key): ?RecipeContainer {
        return $this->recipes[$key] ?? null;
    }

    /**
     * @return RecipeContainer[]
     */
    public function getAll(): array {
        return $this->recipes;
    }

    public function remove(string $key): void {
        unset($this->recipes[$key]);
    }

    public function removeRecipe(string $key, string $name): void {
        if (!$this->exists($key)) return;
        $container = $this->get($key);
        $container->removeRecipe($name);
    }
}