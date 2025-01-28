<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\BlockVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\BlockVariable;
use pocketmine\block\Block;

class BlockArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.block", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getBlock(FlowItemExecutor $executor): Block {
        $block = $this->getVariableName()->eval($executor->getVariableRegistryCopy());
        $variable = $executor->getVariable($block);

        if ($variable instanceof BlockVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.block"], (string)$this->getVariableName()]));
    }

    public function createFormElements(array $variables): array {
        return [
            new BlockVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}