<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\Event;

class EventObjectVariable extends ObjectVariable {

    public function __construct(Event $value, ?string $str = null) {
        $names = explode("\\", $value->getEventName());
        parent::__construct($value, $str ?? end($names));
    }

    public function getProperty(string $name): ?Variable {
        $event = $this->getEvent();
        switch ($name) {
            case "name":
                $names = explode("\\", $event->getEventName());
                return new StringVariable(end($names));
            case "isCanceled":
                return new BooleanVariable($event->isCancelled());
            default:
                return null;
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getEvent(): Event {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "event";
    }

    public static function getValuesDummy(): array {
        return [
            "name" => new DummyVariable(StringVariable::class),
            "isCanceled" => new DummyVariable(BooleanVariable::class),
        ];
    }
}
