<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\utils\Language;

abstract class CheckEntityState extends FlowItem implements Condition, EntityFlowItem {
    use EntityFlowItemTrait;

    protected array $detailDefaultReplace = ["entity"];

    public function __construct(
        string $id,
        string $category = FlowItemCategory::ENTITY,
        string $entity = "",
    ) {
        parent::__construct($id, $category);

        $this->setEntityVariableName($entity);
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== null;
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getEntityVariableName()]);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName()];
    }
}
