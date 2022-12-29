<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Living;
use pocketmine\permission\BanEntry;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use function array_map;
use function microtime;

class ServerObjectVariable extends ObjectVariable {

    public function __construct(Server $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $server = $this->getServer();
        $now = new \DateTime(timezone: Mineflow::getTimeZone());
        switch ($index) {
            case "name":
                return new StringVariable($server->getName());
            case "motd":
                return new StringVariable($server->getMotd());
            case "ip":
                return new StringVariable($server->getIp());
            case "port":
                return new NumberVariable($server->getPort());
            case "start_time":
                return new NumberVariable($server->getStartTime());
            case "tick":
                return new NumberVariable($server->getTick());
            case "microtime":
                return new NumberVariable(microtime(true));
            case "time":
                return new MapVariable([
                    "hours" => new NumberVariable((int)$now->format("H")),
                    "minutes" => new NumberVariable((int)$now->format("i")),
                    "seconds" => new NumberVariable((int)$now->format("s")),
                ], $now->format("H:i:s"));
            case "date":
                return new MapVariable([
                    "year" => new NumberVariable((int)$now->format("Y")),
                    "month" => new NumberVariable((int)$now->format("m")),
                    "day" => new NumberVariable((int)$now->format("d")),
                ], $now->format("m/d"));
            case "default_world":
                $world = $server->getWorldManager()->getDefaultWorld();
                if ($world === null) return null;
                return new WorldObjectVariable($world);
            case "worlds":
                return new ListVariable(array_map(fn(World $world) => new WorldObjectVariable($world), $server->getWorldManager()->getWorlds()));
            case "players":
                return new ListVariable(array_map(fn(Player $player) => new PlayerObjectVariable($player), array_values($server->getOnlinePlayers())));
            case "entities":
                $entities = [];
                foreach ($server->getWorldManager()->getWorlds() as $world) {
                    foreach ($world->getEntities() as $entity) {
                        $entities[] = EntityObjectVariable::fromObject($entity);
                    }
                }
                return new ListVariable($entities);
            case "livings":
                $entities = [];
                foreach ($server->getWorldManager()->getWorlds() as $world) {
                    foreach ($world->getEntities() as $entity) {
                        if (!($entity instanceof Living)) {
                            continue;
                        }
                        $entities[] = EntityObjectVariable::fromObject($entity);
                    }
                }
                return new ListVariable($entities);
            case "ops":
                return new ListVariable(array_map(fn(string $name) => new StringVariable($name), $server->getOps()->getAll(true)));
            case "bans":
                return new ListVariable(array_map(fn(BanEntry $entry) => new StringVariable($entry->getName()), $server->getNameBans()->getEntries()));
            case "ip_bans":
                return new ListVariable(array_map(fn(BanEntry $entry) => new StringVariable($entry->getName()), $server->getIPBans()->getEntries()));
            case "whitelist":
                return new ListVariable(array_map(fn(string $name) => new StringVariable($name), $server->getWhitelisted()->getAll(true)));
            default:
                return parent::getValueFromIndex($index);
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getServer(): Server {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "motd" => new DummyVariable(DummyVariable::STRING),
            "ip" => new DummyVariable(DummyVariable::STRING),
            "port" => new DummyVariable(DummyVariable::NUMBER),
            "start_time" => new DummyVariable(DummyVariable::NUMBER),
            "tick" => new DummyVariable(DummyVariable::NUMBER),
            "default_world" => new DummyVariable(DummyVariable::WORLD),
            "worlds" => new DummyVariable(DummyVariable::LIST, DummyVariable::WORLD),
            "players" => new DummyVariable(DummyVariable::LIST, DummyVariable::PLAYER),
            "entities" => new DummyVariable(DummyVariable::LIST, DummyVariable::ENTITY),
            "livings" => new DummyVariable(DummyVariable::LIST, DummyVariable::ENTITY),
            "ops" => new DummyVariable(DummyVariable::LIST, DummyVariable::STRING),
            "bans" => new DummyVariable(DummyVariable::LIST, DummyVariable::STRING),
            "ip_bans" => new DummyVariable(DummyVariable::LIST, DummyVariable::STRING),
            "whitelist" => new DummyVariable(DummyVariable::LIST, DummyVariable::STRING),
        ]);
    }

    public function __toString(): string {
        return (string)(new MapVariable(self::getValuesDummy()));
    }
}
