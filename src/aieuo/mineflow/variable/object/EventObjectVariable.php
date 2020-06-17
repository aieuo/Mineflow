<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\Event;

class EventObjectVariable extends ObjectVariable {

    public function __construct(Event $value, string $name = "", ?string $str = null) {
        parent::__construct($value, $name, $str ?? $value->getEventName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $event = $this->getEvent();
        switch ($index) {
            case "name":
                $variable = new StringVariable($event->getEventName(), "name");
                break;
            case "isCanceled":
                $variable = new StringVariable($event->isCancelled() ? "true" : "false", "isCanceled");
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getEvent(): Event {
        /** @var Event $value */
        $value = $this->getValue();
        return $value;
    }
}
