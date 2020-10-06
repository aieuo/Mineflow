<?php

namespace aieuo\mineflow\trigger\command;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;

class CommandTrigger extends Trigger {

    public function __construct(string $key, string $subKey = "") {
        parent::__construct(Trigger::TYPE_COMMAND, $key, $subKey);
    }

    /**
     * @param string $command
     * @return array
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($command): array {
        return DefaultVariables::getCommandVariables($command);
    }

    public function getVariablesDummy(): array {
        return [
            new DummyVariable("cmd", DummyVariable::STRING),
            new DummyVariable("args", DummyVariable::LIST),
        ];
    }
}