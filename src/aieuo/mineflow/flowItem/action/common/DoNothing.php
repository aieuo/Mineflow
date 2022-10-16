<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class DoNothing extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct() {
        parent::__construct(self::DO_NOTHING, FlowItemCategory::COMMON);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        yield Await::ALL;
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
