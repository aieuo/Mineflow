<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EventVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EventVariable;
use pocketmine\event\Event;

class EventArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.event", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getEvent(FlowItemExecutor $executor): Event {
        $event = $this->getVariableName()->eval($executor->getVariableRegistryCopy());

        $variable = $executor->getVariable($event);
        if ($variable instanceof EventVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.event"], (string)$this->getVariableName()]));
    }

    public function createTypeMismatchedException(string $eventName): InvalidPlaceholderValueException {
        return new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [
            Language::get("action.target.require.event")."(".$eventName.")",
            (string)$this->getVariableName(),
        ]));
    }

    public function createFormElements(array $variables): array {
        return [
            new EventVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}