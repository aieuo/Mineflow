<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;

class CommandConsole extends Action {

    protected $id = self::COMMAND_CONSOLE;

    protected $name = "action.commandConsole.name";
    protected $detail = "action.commandConsole.detail";
    protected $detailDefaultReplace = ["command"];

    protected $category = Categories::CATEGORY_ACTION_COMMAND;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $command;

    public function __construct(string $command = "") {
        $this->command = $command;
    }

    public function setCommand(string $health) {
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $command = $origin->replaceVariables($this->getCommand());

        Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $command);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.command.form.command", Language::get("form.example", ["mineflow"]), $default[1] ?? $this->getCommand()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setCommand($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getCommand()];
    }
}