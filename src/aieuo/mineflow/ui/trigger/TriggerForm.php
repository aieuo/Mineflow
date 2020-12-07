<?php

namespace aieuo\mineflow\ui\trigger;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use pocketmine\Player;

abstract class TriggerForm {

    abstract public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void;

    abstract public function sendMenu(Player $player, Recipe $recipe): void;
}