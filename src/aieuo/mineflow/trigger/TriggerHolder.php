<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipeContainer;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\Event;

class TriggerHolder {

    public const GLOBAL_INSTANCE_KEY = "global";

    /** @var RecipeContainer[][] */
    private array $recipes = [];

    private static ?TriggerHolder $globalInstance = null;

    /** @var TriggerHolder[] */
    private static array $instances = [];

    public static function global(): self {
        if (self::$globalInstance === null) {
            self::$globalInstance = self::create(self::GLOBAL_INSTANCE_KEY);
        }
        return self::$globalInstance;
    }

    public static function create(string $name): self {
        if (isset(self::$instances[$name])) {
            throw new \InvalidArgumentException("TriggerHolder {$name} is already created.");
        }

        $holder = new self();
        self::addInstance($name, $holder);
        return $holder;
    }

    public static function getInstance(string $name = self::GLOBAL_INSTANCE_KEY): ?self {
        return self::$instances[$name] ?? null;
    }

    public static function addInstance(string $name, self $holder): void {
        self::$instances[$name] = $holder;
    }

    public static function getInstances(): array {
        return self::$instances;
    }

    /**
     * @param Trigger $trigger
     * @param Entity|null $target
     * @param array<string, Variable> $variables
     * @param Event|null $event
     * @return int
     */
    public static function executeRecipeAll(Trigger $trigger, ?Entity $target, array $variables, ?Event $event): int {
        $executed = 0;
        foreach (self::getInstances() as $holder) {
            $executed += $holder->getRecipes($trigger)?->executeAll($target, $variables, $event);
        }
        return $executed;
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