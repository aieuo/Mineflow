<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\data\java\GameModeIdMap;
use SOFe\AwaitGenerator\Await;

class Gamemode extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private array $gamemodes = [
        "action.gamemode.survival",
        "action.gamemode.creative",
        "action.gamemode.adventure",
        "action.gamemode.spectator"
    ];

    private PlayerPlaceholder $player;

    public function __construct(string $player = "", private int $gamemode = 0) {
        parent::__construct(self::GAMEMODE, FlowItemCategory::PLAYER);

        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "gamemode"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), Language::get($this->gamemodes[$this->getGamemode()])];
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    public function setGamemode(int $gamemode): void {
        $this->gamemode = $gamemode;
    }

    public function getGamemode(): int {
        return $this->gamemode;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $gamemode = GameModeIdMap::getInstance()->fromId($this->getGamemode());

        yield Await::ALL;
        return $player->getGamemode() === $gamemode;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new Dropdown("@condition.gamemode.form.gamemode", array_map(fn(string $mode) => Language::get($mode), $this->gamemodes), $this->getGamemode()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setGamemode($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getGamemode()];
    }
}
