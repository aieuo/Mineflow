<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;

abstract class SetHealthBase extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected array $detailDefaultReplace = ["entity", "health"];

    public function __construct(
        string $id,
        string $category = FlowItemCategory::ENTITY,
        string $entity = "",
        private string $health = ""
    ) {
        parent::__construct($id, $category);

        $this->setEntityVariableName($entity);
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