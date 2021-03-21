<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class Gamemode extends FlowItem implements Condition, PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::GAMEMODE;

    protected $name = "condition.gamemode.name";
    protected $detail = "condition.gamemode.detail";
    protected $detailDefaultReplace = ["player", "gamemode"];

    protected $category = Category::PLAYER;

    private $gamemodes = [
        "action.gamemode.survival",
        "action.gamemode.creative",
        "action.gamemode.adventure",
        "action.gamemode.spectator"
    ];

    /** @var int */
    private $gamemode;

    public function __construct(string $player = "", int $mode = Player::SURVIVAL) {
        $this->setPlayerVariableName($player);
        $this->gamemode = $mode;
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), Language::get($this->gamemodes[$this->getGamemode()])]);
    }

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $gamemode = $this->getGamemode();

        yield true;
        return $player->getGamemode() === $gamemode;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new Dropdown("@condition.gamemode.form.gamemode", array_map(function (string $mode) {
                return Language::get($mode);
            }, $this->gamemodes), $this->getGamemode()),
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