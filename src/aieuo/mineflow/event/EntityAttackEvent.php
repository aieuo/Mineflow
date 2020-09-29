<?php

namespace aieuo\mineflow\event;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class EntityAttackEvent extends EntityDamageByEntityEvent {
    public function __construct(Entity $damager, Entity $entity, int $cause, float $damage, array $modifiers = [], float $knockBack = 0.4) {
        parent::__construct($damager, $entity, $cause, $damage, $modifiers, $knockBack);
    }
}