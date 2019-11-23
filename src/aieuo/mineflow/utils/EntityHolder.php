<?php

namespace aieuo\mineflow\utils;

use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\Player;

class EntityHolder {

    /** @var self */
    private static $instance;

    /** @var Entity[] */
    private $entities = [];

    public function __construct() {
        self::$instance = $this;
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getPlayerByName(string $name): ?Player {
        $player = Server::getInstance()->getPlayer($name);
        if ($player instanceof Player) $this->entities[$player->getId()] = $player;
        return $player;
    }

    public function findEntity(int $id): ?Entity {
        if ($id > Entity::$entityCount) return null;
        if (isset($this->entities[$id])) {
            $entity = $this->entities[$id];
            if (!$entity->isAlive() or $entity->isClosed() or ($entity instanceof Player and !$entity->isOnline())) {
                $this->entities[$id] = null;
                return null;
            }
            return $entity;
        }
        $levels = Server::getInstance()->getLevels();
        foreach ($levels as $level) {
            $entity = $level->getEntity($id);
            if ($entity instanceof Entity) break;
        }
        $this->entities[$id] = $entity;
        if (empty($entity)) return null;
        return $entity;
    }

    public function isPlayer(int $id): bool {
        $entity = $this->findEntity($id);
        return $entity instanceof Player;
    }

    public function isActive(int $id): bool {
        $entity = $this->findEntity($id);
        if ($entity === null) return false;
        return $entity->isAlive() and !$entity->isClosed() and !($entity instanceof Player and !$entity->isOnline());
    }
}