<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EventVariable;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\event\Event;

#[Deprecated]
/**
 * @see EventPlaceholder
 */
trait EventFlowItemTrait {

    /* @var string[] */
    private array $eventVariableNames = [];

    public function getEventVariableName(string $name = ""): string {
        return $this->eventVariableNames[$name] ?? "";
    }

    public function setEventVariableName(string $event, string $name = ""): void {
        $this->eventVariableNames[$name] = $event;
    }

    public function getEvent(FlowItemExecutor $source, string $name = ""): Event {
        $event = $source->replaceVariables($rawName = $this->getEventVariableName($name));

        $variable = $source->getVariable($event);
        if ($variable instanceof EventVariable) {
            return $variable->getValue();
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.event"], $rawName]));
    }

    public function createTypeMismatchedException(string $variableName, string $eventName): InvalidFlowValueException {
        return new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [
            Language::get("action.target.require.event")."(".$eventName.")",
            $variableName,
        ]));
    }
}
