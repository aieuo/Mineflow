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

class SetHealth extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::SET_HEALTH;

    protected string $name = "action.setHealth.name";
    protected string $detail = "action.setHealth.detail";
    protected array $detailDefaultReplace = ["entity", "health"];

    protected string $category = Category::ENTITY;

    private string $health;

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
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getHealth()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $health = $source->replaceVariables($this->getHealth());

        $this->throwIfInvalidNumber($health, 1, null);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setHealth((float)$health);
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleNumberInput("@action.setHealth.form.health", "20", $this->getHealth(), true, 1),
        ];
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