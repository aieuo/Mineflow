<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;

class SaveData extends FlowItem {

    protected string $id = self::SAVE_DATA;

    protected string $name = "action.saveData.name";
    protected string $detail = "action.saveData.detail";

    protected string $category = FlowItemCategory::SCRIPT;

    public function isDataValid(): bool {
        return true;
    }

    public function execute(FlowItemExecutor $source): \Generator {
        Mineflow::getRecipeManager()->saveAll();
        Mineflow::getFormManager()->saveAll();
        Mineflow::getVariableHelper()->saveAll();
        yield true;
    }

    public function loadSaveData(array $content): FlowItem {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}
