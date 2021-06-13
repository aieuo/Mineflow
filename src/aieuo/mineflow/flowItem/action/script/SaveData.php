<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;

class SaveData extends FlowItem {

    protected string $id = self::SAVE_DATA;

    protected string $name = "action.saveData.name";
    protected string $detail = "action.saveData.detail";

    protected string $category = Category::SCRIPT;

    public function isDataValid(): bool {
        return true;
    }

    public function execute(FlowItemExecutor $source): \Generator {
        Main::getRecipeManager()->saveAll();
        Main::getFormManager()->saveAll();
        Main::getVariableHelper()->saveAll();
        yield true;
    }

    public function loadSaveData(array $content): FlowItem {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}
