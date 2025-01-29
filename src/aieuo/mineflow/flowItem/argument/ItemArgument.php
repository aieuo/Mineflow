<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\item\Item;

class ItemArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.item", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getItemVariable(FlowItemExecutor $executor): ItemVariable {
        $item = $this->getVariableName()->eval($executor->getVariableRegistryCopy());

        $variable = $executor->getVariable($item);
        if (!($variable instanceof ItemVariable)) {
            throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.item"], (string)$this->getVariableName()]));
        }

        return $variable;
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getItem(FlowItemExecutor $executor): Item {
        $variable = $this->getItemVariable($executor);
        if (!(($item = $variable->getValue()) instanceof Item)) {
            throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.item"], (string)$this->getVariableName()]));
        }

        return $item;
    }

    public function createFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}