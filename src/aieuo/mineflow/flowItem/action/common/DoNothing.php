<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;

class DoNothing extends FlowItem {

    protected $id = self::DO_NOTHING;

    protected $name = "action.doNothing.name";
    protected $detail = "action.doNothing.detail";

    protected $category = Category::COMMON;

    public function execute(FlowItemExecutor $source): \Generator {
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