<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;

class DoNothing extends FlowItem {

    protected string $id = self::DO_NOTHING;

    protected string $name = "action.doNothing.name";
    protected string $detail = "action.doNothing.detail";

    protected string $category = Category::COMMON;

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