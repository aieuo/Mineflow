<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class ExecuteRecipe extends ExecuteRecipeBase {

    public function __construct(string $name = "", array $args = []) {
        parent::__construct(self::EXECUTE_RECIPE, recipeName: $name, args: $args);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $recipe = clone $this->getRecipe($source);
        $args = $this->getArgs()->getVariableArray($source);

        $recipe->executeAllTargets($source->getTarget(), $source->getVariables(), $source->getEvent(), $args);

        yield Await::ALL;
    }
}