<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class LookAt extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionPlaceholder $position;

    public function __construct(string $entity = "", string $position = "") {
        parent::__construct(self::LOOK_AT, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
        $this->position = new PositionPlaceholder("position", $position);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", $this->position->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->position->get()];
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->position->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getOnlineEntity($source);
        $position = $this->position->getPosition($source);

        if ($entity instanceof Living) {
            $entity->lookAt($position);
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            $this->position->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setEntityVariableName($content[0]);
        $this->position->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->position->get()];
    }
}
