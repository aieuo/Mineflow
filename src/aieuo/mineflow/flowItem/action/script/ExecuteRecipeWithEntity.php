<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\RecipeArgumentArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class ExecuteRecipeWithEntity extends ExecuteRecipeBase {

    public function __construct(string $recipeName = "", string $entity = "", array $args = []) {
        parent::__construct(self::EXECUTE_RECIPE_WITH_ENTITY, recipeName: $recipeName, args: $args);

        $this->setArguments([
            StringArgument::create("name", $recipeName, "@action.executeRecipe.form.name")->example("aieuo"),
            EntityArgument::create("target", $entity),
            RecipeArgumentArgument::create("args", $args)->recipeName(fn() => $this->getRecipeName()->getRawString()),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("target");
    }

    public function getArgs(): RecipeArgumentArgument {
        return $this->getArgument("args");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $recipe = clone $this->getRecipe($source);
        $args = $this->getArgs()->getVariableArray($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $recipe->execute($entity, $source->getEvent(), $source->getVariables(), $args);

        yield Await::ALL;
    }
}