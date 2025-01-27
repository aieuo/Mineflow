<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\VariableProperty;
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

    public function getEventName(Event $event): string {
        $names = explode("\\", $event->getEventName());
        return end($names);
    }

    public function __toString(): string {
        return $this->getEventName($this->getValue());
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty($class, "name", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(EventVariable $variable) => new StringVariable((string)$variable),
            passVariable: true
        ));
        self::registerProperty($class, "canceled", new VariableProperty(
            new DummyVariable(BooleanVariable::class),
            fn(Event $event) => new BooleanVariable($event instanceof Cancellable and $event->isCancelled()),
        ), aliases: ["isCanceled"]);
    }
}