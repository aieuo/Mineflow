<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SetYaw extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $entity = "", private string $yaw = "") {
        parent::__construct(self::SET_YAW, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", "yaw"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->getYaw()];
    }

    public function setYaw(string $yaw): self {
        $this->yaw = $yaw;
        return $this;
    }

    public function getYaw(): string {
        return $this->yaw;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->yaw !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $yaw = $this->getFloat($source->replaceVariables($this->getYaw()));
        $entity = $this->getOnlineEntity($source);

        $entity->setRotation($yaw, $entity->getLocation()->getPitch());
        if ($entity instanceof Player) $entity->teleport($entity->getPosition(), $yaw, $entity->getLocation()->getPitch());

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleNumberInput("@action.setYaw.form.yaw", "180", $this->getYaw(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setYaw($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getYaw()];
    }
}
