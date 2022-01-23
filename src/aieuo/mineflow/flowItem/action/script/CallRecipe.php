<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

class CallRecipe extends ExecuteRecipe {

    protected string $id = self::CALL_RECIPE;

    protected string $name = "action.callRecipe.name";
    protected string $detail = "action.callRecipe.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $recipe = clone $this->getRecipe($source);
        $args = $this->getArguments($source);

        $recipe->executeAllTargets($source->getTarget(), [
            "parent" => $source->getVariable("this"),
        ], $source->getEvent(), $args, $source);
        yield false;
    }
}