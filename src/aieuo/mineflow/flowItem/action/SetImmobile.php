<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class SetImmobile extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_IMMOBILE;

    protected $name = "action.setImmobile.name";
    protected $detail = "action.setImmobile.detail";
    protected $detailDefaultReplace = ["entity"];

    protected $category = Category::ENTITY;

    public function __construct(string $entity = "") {
        $this->setEntityVariableName($entity);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "";
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setImmobile(true);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new EntityVariableDropdown($variables, $this->getEntityVariableName()),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName()];
    }
}
