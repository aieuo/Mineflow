<?php

namespace aieuo\mineflow\flowItem\action\world;

use pocketmine\entity\Entity;
use pocketmine\player\Player;

class GetPlayersInArea extends GetEntitiesInArea {

    protected string $id = self::GET_PLAYERS_IN_AREA;

    protected string $name = "action.getPlayersInArea.name";
    protected string $detail = "action.getPlayersInArea.detail";

    public function __construct(string $aabb = "", string $worldName = "", string $resultName = "players") {
        parent::__construct($aabb, $worldName, $resultName);
    }

    /**
     * @param Entity[] $entities
     * @return Entity[]
     */
    protected function filterEntities(array $entities): array {
        return array_filter($entities, fn(Entity $entity) => $entity instanceof Player);
    }
}