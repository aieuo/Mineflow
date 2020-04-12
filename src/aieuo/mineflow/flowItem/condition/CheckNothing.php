<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;

class CheckNothing extends Condition {

    protected $id = self::CHECK_NOTHING;

    protected $name = "condition.noCheck.name";
    protected $detail = "condition.noCheck.detail";

    protected $category = Category::COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(Recipe $origin): bool {
        return true;
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