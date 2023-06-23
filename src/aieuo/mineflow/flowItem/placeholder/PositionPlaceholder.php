<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\placeholder;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;

class PositionPlaceholder extends Placeholder {

    public function __construct(string $name, string $value = "", string $description = null) {
        parent::__construct($name, $value, $description ?? "@action.form.target.position");
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getPosition(FlowItemExecutor $executor): Position {
        $position = $executor->replaceVariables($this->get());

        $variable = $executor->getVariable($position);
        if ($variable instanceof PositionVariable and ($position = $variable->getValue()) instanceof Position) {
            return $position;
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.position"], $this->get()]));
    }

    public function createFormElement(array $variables): Element {
        return new PositionVariableDropdown($variables, $this->get());
    }
}