<?php
declare(strict_types=1);

namespace aieuo\mineflow\event;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

class MineflowRecipeExecuteEvent extends Event implements Cancellable {
    use CancellableTrait;

    /**
     * @param Recipe $recipe
     * @param Entity|null $target
     * @param Variable[] $variables
     */
    public function __construct(
        private Recipe  $recipe,
        private ?Entity $target,
        private array   $variables,
    ) {
    }

    public function getRecipe(): Recipe {
        return $this->recipe;
    }

    public function getTarget(): ?Entity {
        return $this->target;
    }

    public function setTarget(?Entity $target): void {
        $this->target = $target;
    }

    public function getVariables(): array {
        return $this->variables;
    }

    public function setVariables(array $variables): void {
        $this->variables = $variables;
    }
}