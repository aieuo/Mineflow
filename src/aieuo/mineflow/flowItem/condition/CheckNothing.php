<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;

class CheckNothing extends FlowItem implements Condition {

    protected string $name = "condition.checkNothing.name";
    protected string $detail = "condition.checkNothing.detail";

    public function __construct() {
        parent::__construct(self::CHECK_NOTHING, FlowItemCategory::COMMON);
    }

    public function execute(FlowItemExecutor $source): \Generator {
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