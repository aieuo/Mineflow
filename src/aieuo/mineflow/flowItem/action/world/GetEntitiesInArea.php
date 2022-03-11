<?php

namespace aieuo\mineflow\flowItem\action\world;

class GetEntitiesInArea extends GetEntitiesInAreaBase {

    protected string $name = "action.getEntitiesInArea.name";
    protected string $detail = "action.getEntitiesInArea.detail";

    public function __construct(string $aabb = "", string $worldName = "", string $resultName = "players") {
        parent::__construct(self::GET_ENTITIES_IN_AREA, aabb: $aabb, worldName: $worldName, resultName: $resultName);
    }

    protected function filterEntities(array $entities): array {
        return $entities;
    }
}
