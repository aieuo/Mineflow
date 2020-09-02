<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;

class CheckNothing extends FlowItem implements Condition {

    protected $id = self::CHECK_NOTHING;

    protected $name = "condition.noCheck.name";
    protected $detail = "condition.noCheck.detail";

    protected $category = Category::COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(Recipe $origin) {
        yield true;
        return true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): FlowItem {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}