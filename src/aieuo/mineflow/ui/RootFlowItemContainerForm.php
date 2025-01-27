<?php
declare(strict_types=1);


namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\RootFlowItemContainer;
use pocketmine\player\Player;

interface RootFlowItemContainerForm {
    public function returnToContainerMenu(Player $player, RootFlowItemContainer $container): void;
}