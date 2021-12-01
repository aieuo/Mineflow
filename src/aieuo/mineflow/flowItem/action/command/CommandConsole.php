<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\command;

use aieuo\mineflow\command\MineflowConsoleCommandSender;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;

class CommandConsole extends FlowItem {

    protected string $id = self::COMMAND_CONSOLE;

    protected string $name = "action.commandConsole.name";
    protected string $detail = "action.commandConsole.detail";
    protected array $detailDefaultReplace = ["command"];

    protected string $category = Category::COMMAND;

    protected int $permission = self::PERMISSION_LEVEL_1;

    private string $command;

    public function __construct(string $command = "") {
        $this->command = $command;
    }

    public function setCommand(string $health): void {
        $this->command = $health;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function isDataValid(): bool {
        return $this->command !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getCommand()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $command = $source->replaceVariables($this->getCommand());

        Server::getInstance()->dispatchCommand(new MineflowConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $command);
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.command.form.command", "mineflow", $this->getCommand(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setCommand($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getCommand()];
    }
}