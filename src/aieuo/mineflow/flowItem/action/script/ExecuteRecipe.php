<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ExecuteRecipe extends ExecuteRecipeBase {

    public function __construct(string $name = "", string $args = "") {
        parent::__construct(self::EXECUTE_RECIPE, recipeName: $name, args: $args);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $recipe = clone $this->getRecipe($source);
        $args = $this->getArguments($source);

        $recipe->executeAllTargets($source->getTarget(), $source->getVariables(), $source->getEvent(), $args);
        yield true;
    }
}
