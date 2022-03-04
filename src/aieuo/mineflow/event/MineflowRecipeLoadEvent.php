<?php
declare(strict_types=1);

namespace aieuo\mineflow\event;

use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\event\plugin\PluginEvent;

class MineflowRecipeLoadEvent extends PluginEvent {

    public function __construct(
        Main           $owner,
        private Recipe $recipe
    ) {
        parent::__construct($owner);
    }

    public function getRecipe(): Recipe {
        return $this->recipe;
    }
}