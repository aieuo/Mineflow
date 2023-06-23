<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\placeholder;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\EventVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EventVariable;
use pocketmine\event\Event;

class EventPlaceholder extends Placeholder {

    public function __construct(string $name, string $value = "", string $description = null) {
        parent::__construct($name, $value, $description ?? "@action.form.target.event");
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getEvent(FlowItemExecutor $executor): Event {
        $event = $executor->replaceVariables($this->get());

        $variable = $executor->getVariable($event);
        if ($variable instanceof EventVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.event"], $this->get()]));
    }

    public function createTypeMismatchedException(string $eventName): InvalidPlaceholderValueException {
        return new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [
            Language::get("action.target.require.event")."(".$eventName.")",
            $this->get(),
        ]));
    }

    public function createFormElement(array $variables): Element {
        return new EventVariableDropdown($variables, $this->get());
    }
}