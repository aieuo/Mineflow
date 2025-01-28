<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\ServerVariable;
use pocketmine\block\BaseSign;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\Server;

class DefaultVariables {

    public static function getServerVariables(): array {
        $server = Server::getInstance();
        $onlines = array_map(fn(Player $player) => new PlayerVariable($player), array_values($server->getOnlinePlayers()));
        $now = new \DateTime(timezone: Mineflow::getTimeZone());
        return [
            "server_name" => new StringVariable($server->getName()),
            "microtime" => new NumberVariable(microtime(true)),
            "time" => new MapVariable([
                "hours" => new NumberVariable((int)$now->format("H")),
                "minutes" => new NumberVariable((int)$now->format("i")),
                "seconds" => new NumberVariable((int)$now->format("s")),
            ], $now->format("H:i:s")),
            "date" => new MapVariable([
                "year" => new NumberVariable((int)$now->format("Y")),
                "month" => new NumberVariable((int)$now->format("m")),
                "day" => new NumberVariable((int)$now->format("d")),
            ], $now->format("m/d")),
            "default_world" => new StringVariable($server->getWorldManager()->getDefaultWorld()?->getFolderName() ?? ""),
            "onlines" => new ListVariable($onlines),
            "ops" => new ListVariable(array_map(fn(string $name) => new StringVariable($name), $server->getOps()->getAll(true))),
            "server" => new ServerVariable($server),
        ];
    }

    public static function getEntityVariables(Entity $target, string $name = "target"): array {
        return [$name => EntityVariable::fromObject($target)];
    }

    public static function getPlayerVariables(Player $target, string $name = "target"): array {
        return [$name => new PlayerVariable($target)];
    }

    public static function getBlockVariables(Block $block, string $name = "block"): array {
        $variables = [$name => new BlockVariable($block)];
        if ($block instanceof BaseSign) {
            $variables["sign_lines"] = new ListVariable(array_map(fn(string $text) => new StringVariable($text), $block->getText()->getLines()));
        }
        return $variables;
    }

    public static function getCommandVariables(string $command): array {
        $commands = Utils::parseCommandString($command);
        return [
            "cmd" => new StringVariable(array_shift($commands)),
            "args" => new ListVariable(array_map(fn(string $cmd) => new StringVariable($cmd), $commands)),
        ];
    }
}