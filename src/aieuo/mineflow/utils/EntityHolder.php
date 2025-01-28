<?php
declare(strict_types=1);

namespace aieuo\mineflow\utils;

use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\Server;

class EntityHolder {

    /** @var Entity[]|null[] */
    private static array $entities = [];

    public static function getPlayerByName(string $name): ?Player {
        $player = Server::getInstance()->getPlayerExact($name);
        if ($player instanceof Player) self::$entities[$player->getId()] = $player;
        return $player;
    }

    public static function findEntity(int $id): ?Entity {
        $entity = null;
        if (isset(self::$entities[$id])) {
            $entity = self::$entities[$id];
            if (!$entity->isAlive() or $entity->isClosed() or ($entity instanceof Player and !$entity->isOnline())) {
                self::$entities[$id] = null;
                return null;
            }
            return $entity;
        }
        $worlds = Server::getInstance()->getWorldManager()->getWorlds();
        foreach ($worlds as $level) {
            $entity = $level->getEntity($id);
            if ($entity instanceof Entity) break;
        }
        self::$entities[$id] = $entity;
        if (empty($entity)) return null;
        return $entity;
    }

    public static function isPlayer(int $id): bool {
        $entity = self::findEntity($id);
        return $entity instanceof Player;
    }

    public static function isActive(int $id): bool {
        $entity = self::findEntity($id);
        if ($entity === null) return false;
        return $entity->isAlive() and !$entity->isClosed() and !($entity instanceof Player and !$entity->isOnline());
    }
}