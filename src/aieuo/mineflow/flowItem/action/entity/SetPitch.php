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

class SetPitch extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $entity = "", private string $pitch = "") {
        parent::__construct(self::SET_PITCH, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", "pitch"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->getPitch()];
    }

    public function setPitch(string $pitch): self {
        $this->pitch = $pitch;
        return $this;
    }

    public function getPitch(): string {
        return $this->pitch;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->pitch !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pitch = $this->getFloat($source->replaceVariables($this->getPitch()));
        $entity = $this->getOnlineEntity($source);

        $entity->setRotation($entity->getLocation()->getYaw(), $pitch);
        if ($entity instanceof Player) $entity->teleport($entity->getPosition(), $entity->getLocation()->getYaw(), $pitch);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleNumberInput("@action.setPitch.form.pitch", "180", $this->getPitch(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setPitch($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getPitch()];
    }
}
