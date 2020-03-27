<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class SetScale extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_SCALE;

    protected $name = "action.setScale.name";
    protected $detail = "action.setScale.detail";
    protected $detailDefaultReplace = ["entity", "scale"];

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $scale;

    public function __construct(string $name = "target", string $scale = "") {
        $this->entityVariableName = $name;
        $this->scale = $scale;
    }

    public function setScale(string $scale) {
        $this->scale = $scale;
    }

    public function getScale(): string {
        return $this->scale;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->scale !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getScale()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $health = $origin->replaceVariables($this->getScale());

        $this->throwIfInvalidNumber($health, 0, null);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setScale((float)$health);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@action.setScale.form.scale", Language::get("form.example", ["1"]), $default[2] ?? $this->getScale()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $containsVariable = Main::getVariableHelper()->containsVariable($data[2]);
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        } elseif (!$containsVariable and !is_numeric($data[2])) {
            $errors[] = ["@flowItem.error.notNumber", 2];
        } elseif (!$containsVariable and (float)$data[2] < 0) {
            $errors[] = [Language::get("flowItem.error.lessValue", [0]), 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setEntityVariableName($content[0]);
        $this->setScale($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getScale()];
    }
}