<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class IsSneaking extends Condition {

    protected $id = self::IS_SNEAKING;

    protected $name = "@condition.issneaking.name";
    protected $description = "@condition.issneaking.description";
    protected $detail = "@condition.issneaking.detail";

    protected $category = Categories::CATEGORY_CONDITION_OTHER;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return null;

        return $target->isSneaking();
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