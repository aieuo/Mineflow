<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\data\java\GameModeIdMap;

class SetGamemode extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    private array $gamemodes = [
        "action.gamemode.survival",
        "action.gamemode.creative",
        "action.gamemode.adventure",
        "action.gamemode.spectator"
    ];

    public function __construct(string $player = "", private string $gamemode = "") {
        parent::__construct(self::SET_GAMEMODE, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "gamemode"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), Language::get($this->gamemodes[$this->getGamemode()])];
    }

    public function setGamemode(string $gamemode): void {
        $this->gamemode = $gamemode;
    }

    public function getGamemode(): string {
        return $this->gamemode;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->gamemode !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $gamemode = $source->replaceVariables($this->getGamemode());
        $this->throwIfInvalidNumber($gamemode, 0, 3);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->setGamemode(GameModeIdMap::getInstance()->fromId((int)$gamemode));
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new Dropdown("@action.setGamemode.form.gamemode", array_map(fn(string $mode) => Language::get($mode), $this->gamemodes), (int)$this->getGamemode()),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[0], (string)$data[1]];
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
