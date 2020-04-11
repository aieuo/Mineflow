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

class SetHealth extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_HEALTH;

    protected $name = "action.setHealth.name";
    protected $detail = "action.setHealth.detail";
    protected $detailDefaultReplace = ["entity", "health"];

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $health;

    public function __construct(string $name = "target", string $health = "") {
        $this->entityVariableName = $name;
        $this->health = $health;
    }

    public function setHealth(string $health) {
        $this->health = $health;
    }

    public function getHealth(): string {
        return $this->health;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->health !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getHealth()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $health = $origin->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setHealth((float)$health);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@action.setHealth.form.health", Language::get("form.example", ["20"]), $default[2] ?? $this->getHealth()),
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
        } elseif (!$containsVariable and (float)$data[2] < 1) {
            $errors[] = [Language::get("flowItem.error.lessValue", [1]), 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setEntityVariableName($content[0]);
        $this->setHealth($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getHealth()];
    }
}