<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class CheckNothing extends Condition {

    protected $id = self::CHECK_NOTHING;

    protected $name = "@condition.noCheck.name";
    protected $description = "@condition.noCheck.description";
    protected $detail = "@condition.noCheck.detail";

    protected $category = Categories::CATEGORY_CONDITION_COMMON;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        return true;
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