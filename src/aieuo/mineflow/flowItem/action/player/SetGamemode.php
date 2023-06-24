<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\data\java\GameModeIdMap;
use SOFe\AwaitGenerator\Await;

class SetGamemode extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private array $gamemodes = [
        "action.gamemode.survival",
        "action.gamemode.creative",
        "action.gamemode.adventure",
        "action.gamemode.spectator"
    ];

    private PlayerPlaceholder $player;

    public function __construct(string $player = "", private string $gamemode = "") {
        parent::__construct(self::SET_GAMEMODE, FlowItemCategory::PLAYER);

        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "gamemode"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), Language::get($this->gamemodes[$this->getGamemode()])];
    }

    public function setGamemode(string $gamemode): void {
        $this->gamemode = $gamemode;
    }

    public function getGamemode(): string {
        return $this->gamemode;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->gamemode !== "";
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $gamemode = $this->getInt($source->replaceVariables($this->getGamemode()), 0, 3);
        $player = $this->player->getOnlinePlayer($source);

        $player->setGamemode(GameModeIdMap::getInstance()->fromId($gamemode));

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new Dropdown("@action.setGamemode.form.gamemode", array_map(fn(string $mode) => Language::get($mode), $this->gamemodes), (int)$this->getGamemode()),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(1, fn($value) => (string)$value);
        });
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setGamemode($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getGamemode()];
    }
}
