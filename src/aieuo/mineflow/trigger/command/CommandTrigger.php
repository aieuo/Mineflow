<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\command;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use function explode;

class CommandTrigger extends Trigger {

    private string $command;

    public function __construct(private readonly string $fullCommand = "") {
        $this->command = explode(" ", $this->fullCommand)[0];
        parent::__construct(Triggers::COMMAND);
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function getFullCommand(): string {
        return $this->fullCommand;
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

    public function hash(): string|int {
        return $this->fullCommand;
    }

    public function __toString(): string {
        return Language::get("trigger.command.string", [$this->getFullCommand()]);
    }
}