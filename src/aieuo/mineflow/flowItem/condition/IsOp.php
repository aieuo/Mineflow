<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class IsOp extends Condition {

    protected $id = self::IS_OP;

    protected $name = "condition.isOp.name";
    protected $detail = "condition.isOp.detail";

    protected $category = Categories::CATEGORY_CONDITION_COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        return $target->isOp();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): Condition {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}