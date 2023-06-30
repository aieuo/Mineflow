<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form\page;

use pocketmine\player\Player;

abstract class EditPage {
    abstract public function show(Player $player): \Generator;
}
