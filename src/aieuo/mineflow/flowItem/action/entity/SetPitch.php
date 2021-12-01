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
use pocketmine\player\Player;

class SetPitch extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::SET_PITCH;

    protected string $name = "action.setPitch.name";
    protected string $detail = "action.setPitch.detail";
    protected array $detailDefaultReplace = ["entity", "pitch"];

    protected string $category = Category::ENTITY;

    private string $pitch;

    public function __construct(string $entity = "", string $pitch = "") {
        $this->setEntityVariableName($entity);
        $this->pitch = $pitch;
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

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getPitch()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $pitch = $this->getFloat($source->replaceVariables($this->getPitch()));
        $entity = $this->getOnlineEntity($source);

        $entity->setRotation($entity->getLocation()->getYaw(), $pitch);
        if ($entity instanceof Player) $entity->teleport($entity->getPosition(), $entity->getLocation()->getYaw(), $pitch);
        yield FlowItemExecutor::CONTINUE;
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