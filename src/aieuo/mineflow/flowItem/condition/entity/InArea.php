<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use SOFe\AwaitGenerator\Await;

class InArea extends FlowItem implements Condition, EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $entity = "", string $pos1 = "", string $pos2 = "") {
        parent::__construct(self::IN_AREA, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
        $this->setPositionVariableName($pos1, "pos1");
        $this->setPositionVariableName($pos2, "pos2");
    }

    public function getDetailDefaultReplaces(): array {
        return ["target", "pos1", "pos2"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2")];
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getPositionVariableName("pos1") !== "" and $this->getPositionVariableName("pos2") !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getOnlineEntity($source);
        $pos1 = $this->getPosition($source, "pos1");
        $pos2 = $this->getPosition($source, "pos2");
        $pos = $entity->getLocation()->floor();

        yield Await::ALL;
        return $pos->x >= min($pos1->x, $pos2->x) and $pos->x <= max($pos1->x, $pos2->x)
            and $pos->y >= min($pos1->y, $pos2->y) and $pos->y <= max($pos1->y, $pos2->y)
            and $pos->z >= min($pos1->z, $pos2->z) and $pos->z <= max($pos1->z, $pos2->z);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new PositionVariableDropdown($variables, $this->getPositionVariableName("pos1"), "@condition.inArea.form.pos1"),
            new PositionVariableDropdown($variables, $this->getPositionVariableName("pos2"), "@condition.inArea.form.pos2"),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setEntityVariableName($content[0]);
        $this->setPositionVariableName($content[1], "pos1");
        $this->setPositionVariableName($content[2], "pos2");
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2")];
    }
}
