<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\entity\Living;
use pocketmine\permission\BanEntry;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use function array_map;
use function array_values;

class ServerVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "server";
    }

    public function __construct(private Server $server) {
    }

    public function getValue(): Server {
        return $this->server;
    }

    public function __toString(): string {
        return (string)(new MapVariable(self::getValuesDummy()));
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty(
            $class, "name", new DummyVariable(StringVariable::class),
            fn(Server $server) => new StringVariable($server->getName())
        );
        self::registerProperty(
            $class, "motd", new DummyVariable(StringVariable::class),
            fn(Server $server) => new StringVariable($server->getMotd())
        );
        self::registerProperty(
            $class, "ip", new DummyVariable(StringVariable::class),
            fn(Server $server) => new StringVariable($server->getIp())
        );
        self::registerProperty(
            $class, "port", new DummyVariable(NumberVariable::class),
            fn(Server $server) => new NumberVariable($server->getPort())
        );
        self::registerProperty(
            $class, "tick", new DummyVariable(NumberVariable::class),
            fn(Server $server) => new NumberVariable($server->getTick())
        );
        self::registerProperty(
            $class, "default_world", new DummyVariable(WorldVariable::class),
            function (Server $server) {
                $world = $server->getWorldManager()->getDefaultWorld();
                if ($world === null) return null;
                return new WorldVariable($world);
            }
        );
        self::registerProperty(
            $class, "worlds", new DummyVariable(ListVariable::class, WorldVariable::getTypeName()),
            fn(Server $server) => new ListVariable(array_map(fn(World $world) => new WorldVariable($world), $server->getWorldManager()->getWorlds()))
        );
        self::registerProperty(
            $class, "players", new DummyVariable(ListVariable::class, PlayerVariable::getTypeName()),
            fn(Server $server) => new ListVariable(array_map(fn(Player $player) => new PlayerVariable($player), array_values($server->getOnlinePlayers())))
        );
        self::registerProperty(
            $class, "entities", new DummyVariable(ListVariable::class, EntityVariable::getTypeName()),
            function (Server $server) {
                $entities = [];
                foreach ($server->getWorldManager()->getWorlds() as $world) {
                    foreach ($world->getEntities() as $entity) {
                        $entities[] = EntityVariable::fromObject($entity);
                    }
                }
                return new ListVariable($entities);
            }
        );
        self::registerProperty(
            $class, "livings", new DummyVariable(ListVariable::class, EntityVariable::getTypeName()),
            function (Server $server) {
                $entities = [];
                foreach ($server->getWorldManager()->getWorlds() as $world) {
                    foreach ($world->getEntities() as $entity) {
                        if (!($entity instanceof Living)) {
                            continue;
                        }
                        $entities[] = EntityVariable::fromObject($entity);
                    }
                }
                return new ListVariable($entities);
            }
        );
        self::registerProperty(
            $class, "ops", new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
            fn(Server $server) => new ListVariable(array_map(fn(string $name) => new StringVariable($name), $server->getOps()->getAll(true)))
        );
        self::registerProperty(
            $class, "bans", new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
            fn(Server $server) => new ListVariable(array_map(fn(BanEntry $entry) => new StringVariable($entry->getName()), $server->getNameBans()->getEntries()))
        );
        self::registerProperty(
            $class, "ip_bans", new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
            fn(Server $server) => new ListVariable(array_map(fn(BanEntry $entry) => new StringVariable($entry->getName()), $server->getIPBans()->getEntries()))
        );
        self::registerProperty(
            $class, "whitelist", new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
            fn(Server $server) => new ListVariable(array_map(fn(string $name) => new StringVariable($name), $server->getWhitelisted()->getAll(true)))
        );
    }
}
