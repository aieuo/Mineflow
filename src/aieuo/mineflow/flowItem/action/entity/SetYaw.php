<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class SetYaw extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $name = "action.setYaw.name";
    protected string $detail = "action.setYaw.detail";
    protected array $detailDefaultReplace = ["entity", "yaw"];

    private string $yaw;

    public function __construct(string $entity = "", string $pitch = "") {
        parent::__construct(self::SET_YAW, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
        $this->yaw = $pitch;
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getYaw()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $yaw = $source->replaceVariables($this->getYaw());
        $this->throwIfInvalidNumber($yaw);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setRotation((float)$yaw, $entity->getLocation()->getPitch());
        if ($entity instanceof Player) $entity->teleport($entity->getPosition(), (float)$yaw, $entity->getLocation()->getPitch());
        yield true;
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