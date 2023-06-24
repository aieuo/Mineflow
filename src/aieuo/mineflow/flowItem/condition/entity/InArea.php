<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use SOFe\AwaitGenerator\Await;

class InArea extends FlowItem implements Condition, EntityFlowItem {
    use EntityFlowItemTrait;
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionPlaceholder $position1;
    private PositionPlaceholder $position2;

    public function __construct(string $entity = "", string $pos1 = "", string $pos2 = "") {
        parent::__construct(self::IN_AREA, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
        $this->position1 = new PositionPlaceholder("pos1", $pos1, "@condition.inArea.form.pos1");
        $this->position2 = new PositionPlaceholder("pos2", $pos2, "@condition.inArea.form.pos2");
    }

    public function getDetailDefaultReplaces(): array {
        return ["target", "pos1", "pos2"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->position1->get(), $this->position2->get()];
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->position1->isNotEmpty() and $this->position2->isNotEmpty();
    }

    public function getPosition1(): PositionPlaceholder {
        return $this->position1;
    }

    public function getPosition2(): PositionPlaceholder {
        return $this->position2;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getOnlineEntity($source);
        $pos1 = $this->position1->getPosition($source);
        $pos2 = $this->position2->getPosition($source);
        $pos = $entity->getLocation()->floor();

        yield Await::ALL;
        return $pos->x >= min($pos1->x, $pos2->x) and $pos->x <= max($pos1->x, $pos2->x)
            and $pos->y >= min($pos1->y, $pos2->y) and $pos->y <= max($pos1->y, $pos2->y)
            and $pos->z >= min($pos1->z, $pos2->z) and $pos->z <= max($pos1->z, $pos2->z);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            $this->position1->createFormElement($variables),
            $this->position2->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setEntityVariableName($content[0]);
        $this->position1->set($content[1]);
        $this->position2->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->position1->get(), $this->position2->get()];
    }
}
