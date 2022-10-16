<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class LookAt extends FlowItem implements EntityFlowItem, PositionFlowItem {
    use EntityFlowItemTrait, PositionFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $entity = "", string $position = "") {
        parent::__construct(self::LOOK_AT, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
        $this->setPositionVariableName($position);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", "position"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName()];
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getPositionVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $position = $this->getPosition($source);

        if ($entity instanceof Living) {
            $entity->lookAt($position);
        }

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPositionVariableName()];
    }
}
