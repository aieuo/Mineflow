<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class InArea extends FlowItem implements Condition, EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;

    protected string $id = self::IN_AREA;

    protected string $name = "condition.inArea.name";
    protected string $detail = "condition.inArea.detail";
    protected array $detailDefaultReplace = ["target", "pos1", "pos2"];

    protected string $category = Category::ENTITY;

    public function __construct(string $entity = "", string $pos1 = "", string $pos2 = "") {
        $this->setEntityVariableName($entity);
        $this->setPositionVariableName($pos1, "pos1");
        $this->setPositionVariableName($pos2, "pos2");
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getPositionVariableName("pos1") !== "" and $this->getPositionVariableName("pos2") !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2")]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getOnlineEntity($source);
        $pos1 = $this->getPosition($source, "pos1");
        $pos2 = $this->getPosition($source, "pos2");
        $pos = $entity->floor();

        yield FlowItemExecutor::CONTINUE;
        return $pos->x >= min($pos1->x, $pos2->x) and $pos->x <= max($pos1->x, $pos2->x)
            and $pos->y >= min($pos1->y, $pos2->y) and $pos->y <= max($pos1->y, $pos2->y)
            and $pos->z >= min($pos1->z, $pos2->z) and $pos->z <= max($pos1->z, $pos2->z);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new PositionVariableDropdown($variables, $this->getPositionVariableName("pos1"), "@condition.inArea.form.pos1"),
            new PositionVariableDropdown($variables, $this->getPositionVariableName("pos2"), "@condition.inArea.form.pos2"),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setPositionVariableName($content[1], "pos1");
        $this->setPositionVariableName($content[2], "pos2");
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2")];
    }
}