<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class SaveData extends SimpleAction {

    public function __construct() {
        parent::__construct(self::SAVE_DATA, FlowItemCategory::SCRIPT);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        Mineflow::getRecipeManager()->saveAll();
        Mineflow::getFormManager()->saveAll();
        Mineflow::getVariableHelper()->saveAll();

        yield Await::ALL;
    }
}