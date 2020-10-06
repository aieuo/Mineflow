<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerTypes;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

abstract class TriggerForm {

    abstract public function sendAddedTriggerMenu(Player $player, Recipe $recipe, Trigger $trigger, array $messages = []): void;

    abstract public function sendMenu(Player $player, Recipe $recipe): void;
}