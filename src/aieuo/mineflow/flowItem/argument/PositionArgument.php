<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;

class PositionArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.position", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getPosition(FlowItemExecutor $executor): Position {
        $position = $this->getVariableName()->eval($executor->getVariableRegistryCopy());

        $variable = $executor->getVariable($position);
        if ($variable instanceof PositionVariable and ($position = $variable->getValue()) instanceof Position) {
            return $position;
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.position"], (string)$this->getVariableName()]));
    }

    public function createFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}