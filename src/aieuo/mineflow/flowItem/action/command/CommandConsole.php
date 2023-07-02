<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\command;

use aieuo\mineflow\command\MineflowConsoleCommandSender;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class CommandConsole extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(private string $command = "") {
        parent::__construct(self::COMMAND_CONSOLE, FlowItemCategory::COMMAND, [FlowItemPermission::CONSOLE]);
    }

    public function getDetailDefaultReplaces(): array {
        return ["command"];
    }

    public function getDetailReplaces(): array {
        return [$this->getCommand()];
    }

    public function setCommand(string $command): void {
        $this->command = $command;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function isDataValid(): bool {
        return $this->command !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $command = $source->replaceVariables($this->getCommand());

        Server::getInstance()->dispatchCommand(new MineflowConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $command);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.command.form.command", "mineflow", $this->getCommand(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setCommand($content[0]);
    }

    public function serializeContents(): array {
        return [$this->getCommand()];
    }
}
