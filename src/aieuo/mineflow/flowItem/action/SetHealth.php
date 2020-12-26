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

class SetHealth extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::SET_HEALTH;

    protected $name = "action.setHealth.name";
    protected $detail = "action.setHealth.detail";
    protected $detailDefaultReplace = ["entity", "health"];

    protected $category = Category::ENTITY;

    /** @var string */
    private $health;

    public function __construct(string $entity = "", string $health = "") {
        $this->setEntityVariableName($entity);
        $this->health = $health;
    }

    public function setHealth(string $health): void {
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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $health = $origin->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        $entity->setHealth((float)$health);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new EntityVariableDropdown($variables, $this->getEntityVariableName()),
                new ExampleNumberInput("@action.setHealth.form.health", "20", $this->getHealth(), true, 1),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setHealth($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getHealth()];
    }
}