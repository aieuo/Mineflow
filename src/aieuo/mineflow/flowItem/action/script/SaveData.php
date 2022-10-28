<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;
use aieuo\mineflow\Mineflow;

class SaveData extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct() {
        parent::__construct(self::SAVE_DATA, FlowItemCategory::SCRIPT);
    }

    public function isDataValid(): bool {
        return true;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        Mineflow::getRecipeManager()->saveAll();
        Mineflow::getFormManager()->saveAll();
        Mineflow::getVariableHelper()->saveAll();

        yield Await::ALL;
    }

    public function loadSaveData(array $content): FlowItem {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}
