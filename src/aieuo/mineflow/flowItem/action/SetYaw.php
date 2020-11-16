<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class SetYaw extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_YAW;

    protected $name = "action.setYaw.name";
    protected $detail = "action.setYaw.detail";
    protected $detailDefaultReplace = ["entity", "yaw"];

    protected $category = Category::ENTITY;

    /** @var string */
    private $yaw;

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
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getYaw()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $yaw = $origin->replaceVariables($this->getYaw());
        $this->throwIfInvalidNumber($yaw);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setRotation((float)$yaw, $entity->getPitch());
        if ($entity instanceof Player) $entity->teleport($entity, (float)$yaw, $entity->getPitch());
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new EntityVariableDropdown($variables, $this->getEntityVariableName()),
                new ExampleNumberInput("@action.setYaw.form.yaw", "180", $this->getYaw(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
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