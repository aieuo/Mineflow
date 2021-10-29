<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class SetYaw extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::SET_YAW;

    protected string $name = "action.setYaw.name";
    protected string $detail = "action.setYaw.detail";
    protected array $detailDefaultReplace = ["entity", "yaw"];

    protected string $category = Category::ENTITY;

    private string $yaw;

    public function __construct(string $entity = "", string $pitch = "") {
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
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getYaw()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $yaw = $this->getFloat($source->replaceVariables($this->getYaw()));
        $entity = $this->getOnlineEntity($source);

        $entity->setRotation($yaw, $entity->getPitch());
        if ($entity instanceof Player) $entity->teleport($entity, $yaw, $entity->getPitch());
        yield FlowItemExecutor::CONTINUE;
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