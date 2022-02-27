<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\variable\DummyVariable;
use pocketmine\entity\Living;

class GetNearestLiving extends GetNearestEntity {

    protected string $id = self::GET_NEAREST_LIVING;

    protected string $name = "action.getNearestLiving.name";
    protected string $detail = "action.getNearestLiving.detail";

    public function getTargetClass(): string {
        return Living::class;
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(DummyVariable::LIVING)
        ];
    }
}
