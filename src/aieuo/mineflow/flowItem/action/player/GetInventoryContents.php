<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\InventoryObjectVariable;

class GetInventoryContents extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $id = self::GET_INVENTORY_CONTENTS;

    protected string $name = "action.getInventory.name";
    protected string $detail = "action.getInventory.detail";
    protected array $detailDefaultReplace = ["player", "inventory"];

    protected string $category = FlowItemCategory::PLAYER;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private string $resultName;

    public function __construct(string $player = "", string $resultName = "inventory") {
        $this->setPlayerVariableName($player);
        $this->resultName = $resultName;
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
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());

        $entity = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($entity);

        $variable = new InventoryObjectVariable($entity->getInventory());

        $source->addVariable($resultName, $variable);
        yield true;
        return $this->getResultName();
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
            $this->getResultName() => new DummyVariable(DummyVariable::LIST, DummyVariable::ITEM)
        ];
    }
}
