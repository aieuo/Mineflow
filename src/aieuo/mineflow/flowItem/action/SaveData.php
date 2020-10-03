<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;

class SaveData extends FlowItem {

    protected $id = self::SAVE_DATA;

    protected $name = "action.saveData.name";
    protected $detail = "action.saveData.detail";

    protected $category = Category::SCRIPT;

    public function isDataValid(): bool {
        return true;
    }

    public function execute(Recipe $origin) {
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
