<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class CallRecipe extends ExecuteRecipeBase {

    public function __construct(string $name = "", string $args = "") {
        parent::__construct(self::CALL_RECIPE, recipeName: $name, args: $args);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $recipe = clone $this->getRecipe($source);
        $args = $this->getArguments($source);

        yield from Await::promise(fn($resolve) => $recipe->executeAllTargets($source->getTarget(), [
            "parent" => $source->getVariable("this"),
        ], $source->getEvent(), $args, $source, $resolve));
    }
}
