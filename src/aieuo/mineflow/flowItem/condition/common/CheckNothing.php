<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\common;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class CheckNothing extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;

    public function __construct() {
        parent::__construct(self::CHECK_NOTHING, FlowItemCategory::COMMON);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        yield Await::ALL;
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
