<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class IsFlying extends Condition {

    protected $id = self::IS_FLYING;

    protected $name = "@condition.isFlying.name";
    protected $description = "@condition.isFlying.description";
    protected $detail = "@condition.isFlying.detail";

    protected $category = Categories::CATEGORY_CONDITION_COMMON;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return null;

        return $target->isFlying();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function parseFromSaveData(array $content): ?Condition {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}