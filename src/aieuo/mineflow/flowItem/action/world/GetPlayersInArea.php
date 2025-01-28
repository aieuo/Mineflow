<?php

namespace aieuo\mineflow\flowItem\action\world;

use pocketmine\entity\Entity;
use pocketmine\player\Player;

class GetPlayersInArea extends GetEntitiesInAreaBase {

    public function __construct(string $aabb = "", string $worldName = "", string $resultName = "players") {
        parent::__construct(self::GET_PLAYERS_IN_AREA, aabb: $aabb, worldName: $worldName, resultName: $resultName);
    }

    protected function filterEntities(array $entities): array {
        return array_filter($entities, fn(Entity $entity) => $entity instanceof Player);
    }
}