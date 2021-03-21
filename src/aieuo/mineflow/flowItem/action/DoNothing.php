<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;

class DoNothing extends FlowItem {

    protected $id = self::DO_NOTHING;

    protected $name = "action.doNothing.name";
    protected $detail = "action.doNothing.detail";

    protected $category = Category::COMMON;

    public function execute(Recipe $source): \Generator {
        yield true;
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