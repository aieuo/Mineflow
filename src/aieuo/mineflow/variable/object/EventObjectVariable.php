<?php

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

class EventObjectVariable extends ObjectVariable {

    private array $properties = [];

    public function __construct(Event $value, ?string $str = null) {
        parent::__construct($value, $str ?? $this->getEventName($value));
    }

    public function setProperties(array $properties): void {
        $this->properties = $properties;
    }

    public function getProperty(string $name): ?Variable {
        $event = $this->getEvent();
        return match ($name) {
            "name" => new StringVariable($this->getEventName($this->getEvent())),
            "isCanceled" => new BooleanVariable($event instanceof Cancellable and $event->isCancelled()),
            default => $this->properties[$name] ?? null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getEvent(): Event {
        return $this->getValue();
    }

    public function getEventName(Event $event): string {
        $names = explode("\\", $event->getEventName());
        return end($names);
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
