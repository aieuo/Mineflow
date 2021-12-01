<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\BlockObjectVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use aieuo\mineflow\variable\object\ServerObjectVariable;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Sign;

class DefaultVariables {

    public static function getServerVariables(): array {
        $server = Server::getInstance();
        $onlines = array_map(fn(Player $player) => new StringVariable($player->getName()), array_values($server->getOnlinePlayers()));
        return [
            "server_name" => new StringVariable($server->getName()),
            "microtime" => new NumberVariable(microtime(true)),
            "time" => new StringVariable(date("H:i:s")),
            "date" => new StringVariable(date("m/d")),
            "default_world" => new StringVariable($server->getDefaultLevel()->getFolderName()),
            "onlines" => new ListVariable($onlines),
            "ops" => new ListVariable(array_map(fn(string $name) => new StringVariable($name), $server->getOps()->getAll(true))),
            "server" => new ServerObjectVariable($server),
        ];
    }

    public static function getEntityVariables(Entity $target, string $name = "target"): array {
        if ($target instanceof Player) return self::getPlayerVariables($target, $name);
        if ($target instanceof Human) return self::getHumanVariables($target, $name);
        return [$name => new EntityObjectVariable($target, $target->getNameTag())];
    }

    public static function getHumanVariables(Human $target, string $name = "target"): array {
        if ($target instanceof Player) return self::getPlayerVariables($target, $name);
        return [$name => new HumanObjectVariable($target, $target->getName())];
    }

    public static function getPlayerVariables(Player $target, string $name = "target"): array {
        return [$name => new PlayerObjectVariable($target, $target->getName())];
    }

    public static function getBlockVariables(Block $block, string $name = "block"): array {
        $variables = [$name => new BlockObjectVariable($block, $block->getId().":".$block->getDamage())];
        $tile = $block->level->getTile($block);
        if ($tile instanceof Sign) {
            $variables["sign_lines"] = new ListVariable(array_map(fn(string $text) => new StringVariable($text), $tile->getText()));
        }
        return $variables;
    }

    public static function getCommandVariables(string $command): array {
        $commands = explode(" ", $command);
        return [
            "cmd" => new StringVariable(array_shift($commands)),
            "args" => new ListVariable(array_map(fn(string $cmd) => new StringVariable($cmd), $commands)),
        ];
    }
}