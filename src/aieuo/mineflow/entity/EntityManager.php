<?php

namespace aieuo\mineflow\entity;

use pocketmine\entity\Entity;

class EntityManager {
    public static function init() {
        Entity::registerEntity(MineflowHuman::class, true);
    }
}