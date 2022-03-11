<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemVariable;

abstract class GetInventoryContentsBase extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected array $detailDefaultReplace = ["player", "inventory"];

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::PLAYER,
        string         $player = "",
        private string $resultName = "inventory",
    ) {
        parent::__construct($id, $category);

        $this->setPlayerVariableName($player);
    }

    public function setResultName(string $health): void {
        $this->resultName = $health;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->resultName !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getResultName()]);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.form.resultVariableName", "inventory", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(ListVariable::getTypeName(), ItemVariable::getTypeName())
        ];
    }
}
