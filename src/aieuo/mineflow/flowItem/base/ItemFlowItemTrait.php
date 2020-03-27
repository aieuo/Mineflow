<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\item\Item;

trait ItemFlowItemTrait {

    /* @var string */
    private $itemVariableName = "item";

    public function getItemVariableName(): String {
        return $this->itemVariableName;
    }

    public function setItemVariableName(string $name) {
        $this->itemVariableName = $name;
        return $this;
    }

    public function getItem(Recipe $origin): ?Item {
        $name = $origin->replaceVariables($this->getItemVariableName());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof ItemObjectVariable)) return null;
        return $variable->getItem();
    }

    public function throwIfInvalidItem(?Item $player) {
        if (!($player instanceof Item)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.player"]]));
        }
    }
}