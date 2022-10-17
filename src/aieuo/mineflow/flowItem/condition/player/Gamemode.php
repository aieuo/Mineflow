<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\data\java\GameModeIdMap;
use SOFe\AwaitGenerator\Await;

class Gamemode extends FlowItem implements Condition, PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ConditionNameWithMineflowLanguage;

    private array $gamemodes = [
        "action.gamemode.survival",
        "action.gamemode.creative",
        "action.gamemode.adventure",
        "action.gamemode.spectator"
    ];

    public function __construct(string $player = "", private int $gamemode = 0) {
        parent::__construct(self::GAMEMODE, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "gamemode"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), Language::get($this->gamemodes[$this->getGamemode()])];
    }

    public function setGamemode(int $gamemode): void {
        $this->gamemode = $gamemode;
    }

    public function getGamemode(): int {
        return $this->gamemode;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getOnlinePlayer($source);
        $gamemode = GameModeIdMap::getInstance()->fromId($this->getGamemode());

        yield Await::ALL;
        return $player->getGamemode() === $gamemode;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new Dropdown("@condition.gamemode.form.gamemode", array_map(fn(string $mode) => Language::get($mode), $this->gamemodes), $this->getGamemode()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setGamemode($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getGamemode()];
    }
}
