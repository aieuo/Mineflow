<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class SetYaw extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_YAW;

    protected $name = "action.setYaw.name";
    protected $detail = "action.setYaw.detail";
    protected $detailDefaultReplace = ["entity", "yaw"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $yaw;

    public function __construct(string $name = "target", string $pitch = "") {
        $this->entityVariableName = $name;
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $yaw = $origin->replaceVariables($this->getYaw());
        $this->throwIfInvalidNumber($yaw);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setRotation((float)$yaw, $entity->getPitch());
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@action.setYaw.form.yaw", Language::get("form.example", ["180"]), $default[2] ?? $this->getYaw()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        } elseif (!Main::getVariableHelper()->containsVariable($data[2]) and !is_numeric($data[2])) {
            $errors[] = ["@flowItem.error.notNumber", 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();

        $this->setEntityVariableName($content[0]);
        $this->setYaw($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getYaw()];
    }
}