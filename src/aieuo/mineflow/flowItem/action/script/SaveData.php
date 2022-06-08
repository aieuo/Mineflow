<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;

class SaveData extends FlowItem {

    protected string $name = "action.save.name";
    protected string $detail = "action.save.detail";

    public function __construct() {
        parent::__construct(self::SAVE_DATA, FlowItemCategory::SCRIPT);
    }

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
