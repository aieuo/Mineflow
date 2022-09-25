<?php

namespace aieuo\mineflow\trigger\command;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;

class CommandTrigger extends Trigger {

    public static function create(string $key, string $subKey = ""): CommandTrigger {
        return new CommandTrigger($key, $subKey);
    }

    public function __construct(string $key, string $subKey = "") {
        parent::__construct(Triggers::COMMAND, $key, $subKey);
    }

    /**
     * @param string $command
     * @return array
     */
    public function getVariables(mixed $command): array {
        return DefaultVariables::getCommandVariables($command);
    }

    public function getVariablesDummy(): array {
        return [
            "cmd" => new DummyVariable(StringVariable::class),
            "args" => new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
        ];
    }

    public function __toString(): string {
        return Language::get("trigger.command.string", [$this->getSubKey()]);
    }
}
