<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;
use SOFe\AwaitGenerator\Await;

class Kick extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(
        string         $player = "",
        private string $reason = "",
        private bool   $isAdmin = false
    ) {
        parent::__construct(self::KICK, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "reason"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getReason()];
    }

    public function setReason(string $reason): void {
        $this->reason = $reason;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->reason !== "";
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $reason = $source->replaceVariables($this->getReason());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $reason): void {
            $player->kick($reason);
        }), 1);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.kick.form.reason", "aieuo", $this->getReason()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setReason($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getReason()];
    }
}
