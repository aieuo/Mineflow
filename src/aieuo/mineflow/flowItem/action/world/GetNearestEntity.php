<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use pocketmine\entity\Entity;

class GetNearestEntity extends GetNearestEntityBase {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $position = "", int $maxDistance = 100, string $resultName = "entity") {
        parent::__construct(self::GET_NEAREST_ENTITY, position: $position, maxDistance: $maxDistance, resultName: $resultName);
    }

    public function getTargetClass(): string {
        return Entity::class;
    }
}