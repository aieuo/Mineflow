<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use function end;
use function explode;

class EventVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "event";
    }

    public function __construct(private Event $event) {
    }

    public function getValue(): Event {
        return $this->event;
    }

    public function getValueFromIndex(string $index): ?Variable {
        $event = $this->getValue();
        return match ($index) {
            "name" => new StringVariable($this->getEventName($event)),
            "isCanceled" => new BooleanVariable($event instanceof Cancellable ? $event->isCancelled() : false),
            default => parent::getValueFromIndex($index),
        };
    }

    public function getEventName(Event $event): string {
        $names = explode("\\", $event->getEventName());
        return end($names);
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "isCanceled" => new DummyVariable(DummyVariable::BOOLEAN),
        ]);
    }

    public function __toString(): string {
        return $this->getEventName($this->getValue());
    }
}

