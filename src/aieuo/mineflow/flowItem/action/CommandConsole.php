<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;

class CommandConsole extends FlowItem {

    protected $id = self::COMMAND_CONSOLE;

    protected $name = "action.commandConsole.name";
    protected $detail = "action.commandConsole.detail";
    protected $detailDefaultReplace = ["command"];

    protected $category = Category::COMMAND;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $command = $origin->replaceVariables($this->getCommand());

        Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $command);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.command.form.command", "mineflow", $this->getCommand(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setCommand($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getCommand()];
    }
}