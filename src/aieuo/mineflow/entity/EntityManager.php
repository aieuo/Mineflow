<?php
declare(strict_types=1);

namespace aieuo\mineflow\entity;

use pocketmine\entity\Entity;

class EntityManager {
    public static function init(): void {
        Entity::registerEntity(MineflowHuman::class, true);
    }
}