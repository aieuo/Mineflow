<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\command;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class Command extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $player = "", private string $command = "") {
        parent::__construct(self::COMMAND, FlowItemCategory::COMMAND);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "command"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getCommand()];
    }

    public function setCommand(string $command): void {
        $this->command = $command;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->command !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $command = $source->replaceVariables($this->getCommand());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        Server::getInstance()->dispatchCommand($player, $command);

        yield Await::ALL;
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
