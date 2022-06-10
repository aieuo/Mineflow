<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class CallRecipe extends ExecuteRecipeBase {

    public function __construct(string $name = "", string $args = "") {
        parent::__construct(self::CALL_RECIPE, recipeName: $name, args: $args);
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
