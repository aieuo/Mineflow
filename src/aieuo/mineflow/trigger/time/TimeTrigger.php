<?php

namespace aieuo\mineflow\trigger\time;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;

class TimeTrigger extends Trigger {

    /**
     * @param string $hour
     * @param string $minutes
     * @return self
     */
    public static function create(string $hour, string $minutes = ""): Trigger {
        return new TimeTrigger($hour, $minutes);
    }

    public function __construct(string $hour, string $minutes = "") {
        parent::__construct(Triggers::TIME, $hour, $minutes);
    }

    /**
     * @param int $timestamp
     * @return Variable[]
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($timestamp): array {
        return [
            "hour" => new NumberVariable((int)date("H", $timestamp)),
            "minutes" => new NumberVariable((int)date("i", $timestamp)),
            "seconds" => new NumberVariable((int)date("s", $timestamp)),
        ];
    }

    public function getVariablesDummy(): array {
        return [
            "hour" => new DummyVariable(DummyVariable::NUMBER),
            "minutes" => new DummyVariable(DummyVariable::NUMBER),
            "seconds" => new DummyVariable(DummyVariable::NUMBER),
        ];
    }

    public function __toString(): string {
        return Language::get("trigger.time.string", [$this->getKey(), $this->getSubKey()]);
    }
}