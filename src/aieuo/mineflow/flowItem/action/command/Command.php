<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\command;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;

class Command extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $id = self::COMMAND;

    protected string $name = "action.command.name";
    protected string $detail = "action.command.detail";
    protected array $detailDefaultReplace = ["player", "command"];

    protected string $category = Category::COMMAND;

    private string $command;

    public function __construct(string $player = "", string $command = "") {
        $this->setPlayerVariableName($player);
        $this->command = $command;
    }

    public function setCommand(string $health): void {
        $this->command = $health;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->command !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getCommand()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $command = $source->replaceVariables($this->getCommand());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        Server::getInstance()->dispatchCommand($player, $command);
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.command.form.command", "command", $this->getCommand(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setCommand($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getCommand()];
    }
}