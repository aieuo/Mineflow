<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class IsOp extends Condition {

    protected $id = self::IS_OP;

    protected $name = "@condition.isop.name";
    protected $description = "@condition.isop.description";
    protected $detail = "@condition.isop.detail";

    protected $category = Categories::CATEGORY_CONDITION_OTHER;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return null;

        return $target->isOp();
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