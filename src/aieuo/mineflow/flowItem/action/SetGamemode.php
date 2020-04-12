<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

class SetGamemode extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SET_GAMEMODE;

    protected $name = "action.setGamemode.name";
    protected $detail = "action.setGamemode.detail";
    protected $detailDefaultReplace = ["player", "gamemode"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    private $gamemodes = [
        "action.gamemode.survival",
        "action.gamemode.creative",
        "action.gamemode.adventure",
        "action.gamemode.spectator"
    ];

    /** @var string */
    private $gamemode;

    public function __construct(string $name = "target", string $gamemode = "") {
        $this->playerVariableName = $name;
        $this->gamemode = $gamemode;
    }

    public function setGamemode(string $gamemode) {
        $this->gamemode = $gamemode;
    }

    public function getGamemode(): string {
        return $this->gamemode;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->gamemode !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), Language::get($this->gamemodes[$this->getGamemode()])]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $gamemode = $origin->replaceVariables($this->getGamemode());
        $this->throwIfInvalidNumber($gamemode, 0, 3);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->setGamemode((int)$gamemode);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Dropdown("@action.setGamemode.form.gamemode", array_map(function (string $mode) {
                    return Language::get($mode);
                }, $this->gamemodes), intval($default[2] ?? $this->getGamemode())),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        return ["status" => empty($errors), "contents" => [$data[1], (string)$data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setGamemode($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getGamemode()];
    }
}