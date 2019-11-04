<?php

namespace aieuo\mineflow\command\subcommand;

use pocketmine\command\CommandSender;

abstract class MineflowSubcommand {
    abstract public function execute(CommandSender $sender, array $args): void;
}