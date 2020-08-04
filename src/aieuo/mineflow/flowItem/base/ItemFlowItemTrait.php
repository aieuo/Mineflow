<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\item\Item;

trait ItemFlowItemTrait {

    /* @var string[] */
    private $itemVariableNames = [];

    public function getItemVariableName(string $name = ""): string {
        return $this->itemVariableNames[$name];
    }

    public function setItemVariableName(string $item, string $name = "") {
        $this->itemVariableNames[$name] = $item;
        return $this;
    }

    public function getItem(Recipe $origin, string $name = ""): ?Item {
        $item = $origin->replaceVariables($this->getItemVariableName($name));

        $variable = $origin->getVariable($item);
        if (!($variable instanceof ItemObjectVariable)) return null;
        return $variable->getItem();
    }

    public function throwIfInvalidItem(?Item $item) {
        if (!($item instanceof Item)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.item"], $this->getItemVariableName()]));
        }
    }
}