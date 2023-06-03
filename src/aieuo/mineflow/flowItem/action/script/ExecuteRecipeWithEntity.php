<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class ExecuteRecipeWithEntity extends ExecuteRecipeBase implements EntityFlowItem {
    use EntityFlowItemTrait;

    public function __construct(string $name = "", string $entity = "") {
        parent::__construct(self::EXECUTE_RECIPE_WITH_ENTITY, recipeName: $name);

        $this->setEntityVariableName($entity);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "target"];
    }

    public function getDetailReplaces(): array {
        return [$this->getRecipeName(), $this->getEntityVariableName()];
    }

    public function isDataValid(): bool {
        return $this->getRecipeName() !== "" and $this->getEntityVariableName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $recipe = clone $this->getRecipe($source);

        $entity = $this->getOnlineEntity($source);

        $recipe->execute($entity, $source->getEvent(), $source->getVariables());

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.executeRecipe.form.name", "aieuo", $this->getRecipeName(), true),
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->clear();
        });
    }

    public function loadSaveData(array $content): void {
        $this->setRecipeName($content[0]);
        $this->setEntityVariableName($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getRecipeName(), $this->getEntityVariableName()];
    }
}
