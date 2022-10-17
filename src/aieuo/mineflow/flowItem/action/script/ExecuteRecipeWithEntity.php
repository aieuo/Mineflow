<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $recipe = clone $this->getRecipe($source);

        $entity = $this->getOnlineEntity($source);

        $recipe->execute($entity, $source->getEvent(), $source->getVariables());

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.executeRecipe.form.name", "aieuo", $this->getRecipeName(), true),
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
        ];
    }

    public function parseFromFormData(array $data): array {
        return $data;
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setRecipeName($content[0]);
        $this->setEntityVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getRecipeName(), $this->getEntityVariableName()];
    }
}
