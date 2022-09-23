<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\item\Item;

trait ItemFlowItemTrait {

    /* @var string[] */
    private array $itemVariableNames = [];

    public function getItemVariableName(string $name = ""): string {
        return $this->itemVariableNames[$name] ?? "";
    }

    public function setItemVariableName(string $item, string $name = ""): void {
        $this->itemVariableNames[$name] = $item;
    }

    public function getItemVariable(FlowItemExecutor $source, string $name = ""): ItemVariable {
        $item = $source->replaceVariables($rawName = $this->getItemVariableName($name));

        $variable = $source->getVariable($item);
        if (!($variable instanceof ItemVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.item"], $rawName]));
        }

        return $variable;
    }

    public function getItem(FlowItemExecutor $source, string $name = ""): Item {
        $variable = $this->getItemVariable($source, $name);
        if (!(($item = $variable->getItem()) instanceof Item)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.item"], $this->getItemVariableName($name)]));
        }

        return $item;
    }
}