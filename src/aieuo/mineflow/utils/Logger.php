<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\Main;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Logger {

    public static function warning(string $message, ?Entity $player = null): void {
        if ($player instanceof Player and $player->isOnline()) {
            $player->sendMessage(TextFormat::YELLOW.$message);
        } elseif (Main::getInstance()->isEnabledRecipeErrorInConsole()) {
            Main::getInstance()->getLogger()->warning($message);
        }
    }

    public static function info(string $message, ?Entity $player = null): void {
        if ($player instanceof Player and $player->isOnline()) {
            $player->sendMessage(TextFormat::WHITE.$message);
        } elseif (Main::getInstance()->isEnabledRecipeErrorInConsole()) {
            Main::getInstance()->getLogger()->info($message);
        }
    }
}