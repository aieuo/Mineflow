<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\LivingVariable;
use pocketmine\entity\Living;

class GetNearestLiving extends GetNearestEntityBase {

    public function __construct(string $position = "", int $maxDistance = 100, string $resultName = "living") {
        parent::__construct(self::GET_NEAREST_LIVING, position: $position, maxDistance: $maxDistance, resultName: $resultName);
    }

    public function getTargetClass(): string {
        return Living::class;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(LivingVariable::class)
        ];
    }
}