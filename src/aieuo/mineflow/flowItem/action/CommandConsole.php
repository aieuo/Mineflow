<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\command\MineflowConsoleCommandSender;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;

class CommandConsole extends FlowItem {

    protected $id = self::COMMAND_CONSOLE;

    protected $name = "action.commandConsole.name";
    protected $detail = "action.commandConsole.detail";
    protected $detailDefaultReplace = ["command"];

    protected $category = Category::COMMAND;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $command;

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
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getCommand()]);
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $command = $origin->replaceVariables($this->getCommand());

        Server::getInstance()->dispatchCommand(new MineflowConsoleCommandSender(), $command);
        yield true;
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