<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class ExecuteRecipeWithEntity extends ExecuteRecipeBase {

    public function __construct(string $name = "", string $entity = "") {
        parent::__construct(self::EXECUTE_RECIPE_WITH_ENTITY, recipeName: $name);

        $this->addArgument(EntityArgument::create("target", $entity));
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $recipe = clone $this->getRecipe($source);
        $args = $this->getArgs()->getVariableArray($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $recipe->execute($entity, $source->getEvent(), $source->getVariables(), $args);

        yield Await::ALL;
    }
}
