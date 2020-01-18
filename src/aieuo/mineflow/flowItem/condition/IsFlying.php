<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class IsFlying extends Condition {

    protected $id = self::IS_FLYING;

    protected $name = "condition.isFlying.name";
    protected $detail = "condition.isFlying.detail";

    protected $category = Categories::CATEGORY_CONDITION_COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        return $target->isFlying();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): ?Condition {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}