<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;

class SetHealth extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_HEALTH;

    protected $name = "action.setHealth.name";
    protected $detail = "action.setHealth.detail";
    protected $detailDefaultReplace = ["entity", "health"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $health;

    public function __construct(string $entity = "target", string $health = "") {
        $this->setEntityVariableName($entity);
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $health = $origin->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setHealth((float)$health);
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.entity", "target", $default[1] ?? $this->getEntityVariableName(), true),
                new ExampleNumberInput("@action.setHealth.form.health", "20", $default[2] ?? $this->getHealth(), true, 1),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        $this->setEntityVariableName($content[0]);
        $this->setHealth($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getHealth()];
    }
}