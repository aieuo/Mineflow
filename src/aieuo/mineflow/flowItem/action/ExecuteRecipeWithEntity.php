<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;

class ExecuteRecipeWithEntity extends ExecuteRecipe implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::EXECUTE_RECIPE_WITH_ENTITY;

    protected $name = "action.executeRecipeWithEntity.name";
    protected $detail = "action.executeRecipeWithEntity.detail";
    protected $detailDefaultReplace = ["name", "target"];

    public function __construct(string $name = "", string $entity = "") {
        parent::__construct($name);
        $this->setEntityVariableName($entity);
    }

    public function isDataValid(): bool {
        return $this->getRecipeName() !== "" and $this->getEntityVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getRecipeName(), $this->getEntityVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $recipe = clone $this->getRecipe($source);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $recipe->execute($entity, $source->getEvent(), $source->getVariables());
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.executeRecipe.form.name", "aieuo", $this->getRecipeName(), true),
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
        ];
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => $data];
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