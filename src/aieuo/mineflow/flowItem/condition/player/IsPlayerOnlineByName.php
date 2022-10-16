<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use pocketmine\player\Player;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class IsPlayerOnlineByName extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;

    public function __construct(private string $playerName = "target") {
        parent::__construct(self::IS_PLAYER_ONLINE_BY_NAME, FlowItemCategory::PLAYER);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerName()];
    }

    public function getPlayerName(): string {
        return $this->playerName;
    }

    public function setPlayerName(string $playerName): void {
        $this->playerName = $playerName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerName() !== null;
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getPlayerName());

        $player = Server::getInstance()->getPlayerExact($name);

        yield Await::ALL;
        return $player instanceof Player;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@condition.isPlayerOnline.form.name", "target", $this->getPlayerName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerName()];
    }
}
