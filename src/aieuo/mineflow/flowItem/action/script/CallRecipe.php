<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class CallRecipe extends ExecuteRecipeBase {

    protected string $name = "action.callRecipe.name";
    protected string $detail = "action.callRecipe.detail";

    public function __construct(string $name = "", string $args = "") {
        parent::__construct(self::CALL_RECIPE, name: $name, args: $args);
    }

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