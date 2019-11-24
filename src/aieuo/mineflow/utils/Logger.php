<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\Main;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Logger {

    public static function warning(string $message, ?Player $player = null): void {
        if ($player instanceof Player) {
            $player->sendMessage(TextFormat::YELLOW.$message);
        } else {
            Main::getInstance()->getLogger()->warning($message);
        }
    }
}