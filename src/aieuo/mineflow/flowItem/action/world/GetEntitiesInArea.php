<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

class GetEntitiesInArea extends GetEntitiesInAreaBase {

    public function __construct(string $aabb = "", string $worldName = "", string $resultName = "players") {
        parent::__construct(self::GET_ENTITIES_IN_AREA, aabb: $aabb, worldName: $worldName, resultName: $resultName);
    }

    protected function filterEntities(array $entities): array {
        return $entities;
    }
}