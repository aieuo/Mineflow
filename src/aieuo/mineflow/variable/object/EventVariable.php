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

class EventVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "event";
    }

    public function __construct(Event $value, ?string $str = null) {
        parent::__construct($value, $str ?? $this->getEventName($value));
    }

    public function getValueFromIndex(string $index): ?Variable {
        $event = $this->getEvent();
        return match ($index) {
            "name" => new StringVariable($this->getEventName($event)),
            "isCanceled" => new BooleanVariable($event instanceof Cancellable ? $event->isCancelled() : false),
            default => parent::getValueFromIndex($index),
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

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "isCanceled" => new DummyVariable(DummyVariable::BOOLEAN),
        ]);
    }
}

