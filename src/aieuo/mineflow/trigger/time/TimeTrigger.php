<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\time;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;

class TimeTrigger extends Trigger {

    public function __construct(private readonly int $hours, private readonly int $minutes) {
        parent::__construct(Triggers::TIME);
    }

    public function getHours(): int {
        return $this->hours;
    }

    public function getMinutes(): int {
        return $this->minutes;
    }

    /**
     * @param int $timestamp
     * @return Variable[]
     */
    public function getVariables(mixed $timestamp): array {
        return [
            "hour" => new NumberVariable((int)date("H", $timestamp)),
            "minutes" => new NumberVariable((int)date("i", $timestamp)),
            "seconds" => new NumberVariable((int)date("s", $timestamp)),
        ];
    }

    public function getVariablesDummy(): array {
        return [
            "hour" => new DummyVariable(NumberVariable::class),
            "minutes" => new DummyVariable(NumberVariable::class),
            "seconds" => new DummyVariable(NumberVariable::class),
        ];
    }

    public function hash(): string|int {
        return ($this->hours * 60) + $this->minutes;
    }

    public function __toString(): string {
        return Language::get("trigger.time.string", [$this->getHours(), $this->getMinutes()]);
    }
}