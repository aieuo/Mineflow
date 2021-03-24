<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\item\Item;

class GetInventoryContents extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::GET_INVENTORY_CONTENTS;

    protected $name = "action.getInventory.name";
    protected $detail = "action.getInventory.detail";
    protected $detailDefaultReplace = ["player", "inventory"];

    protected $category = Category::PLAYER;

    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $resultName;

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

        $variable = new ListVariable(array_map(function (Item $item) {
            return new ItemObjectVariable($item);
        }, $entity->getInventory()->getContents()));

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
        return [new DummyVariable($this->getResultName(), DummyVariable::LIST)];
    }
}