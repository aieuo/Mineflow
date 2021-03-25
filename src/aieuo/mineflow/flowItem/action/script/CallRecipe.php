<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

class CallRecipe extends ExecuteRecipe {

    protected $id = self::CALL_RECIPE;

    protected $name = "action.callRecipe.name";
    protected $detail = "action.callRecipe.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $recipe = clone $this->getRecipe($source);
        $args = $this->getArguments($source);

        $recipe->executeAllTargets($source->getTarget(), [], $source->getEvent(), $args, $source);
        yield false;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.executeRecipe.form.name", "aieuo", $this->getRecipeName(), true),
            new ExampleInput("@action.callRecipe.form.args", "{target}, 1, aieuo", implode(", ", $this->getArgs())),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setRecipeName($content[0]);
        $this->setArgs($content[1]);
        return $this;
    }
}