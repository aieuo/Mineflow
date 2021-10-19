<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;

class CheckNothing extends FlowItem implements Condition {

    protected string $id = self::CHECK_NOTHING;

    protected string $name = "condition.noCheck.name";
    protected string $detail = "condition.noCheck.detail";

    protected string $category = Category::COMMON;

    public function execute(FlowItemExecutor $source): \Generator {
        FlowItemExexutor::CONTINUE;
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