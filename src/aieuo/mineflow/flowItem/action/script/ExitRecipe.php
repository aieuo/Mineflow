<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;

class ExitRecipe extends FlowItem {

    protected $id = self::EXIT_RECIPE;

    protected $name = "action.exit.name";
    protected $detail = "action.exit.detail";

    protected $category = Category::SCRIPT;

    public function execute(FlowItemExecutor $source): \Generator {
        $source->exit();
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