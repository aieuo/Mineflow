<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;

class DoNothing extends FlowItem {

    protected string $name = "action.doNothing.name";
    protected string $detail = "action.doNothing.detail";

    public function __construct() {
        parent::__construct(self::DO_NOTHING, FlowItemCategory::COMMON);
    }

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