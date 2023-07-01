<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\IntEnumArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\data\java\GameModeIdMap;
use SOFe\AwaitGenerator\Await;

class Gamemode extends SimpleCondition {

    private array $gamemodes = [
        "action.gamemode.survival",
        "action.gamemode.creative",
        "action.gamemode.adventure",
        "action.gamemode.spectator"
    ];

    private PlayerArgument $player;
    private IntEnumArgument $gamemode;

    public function __construct(string $player = "", int $gamemode = 0) {
        parent::__construct(self::GAMEMODE, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->gamemode = new IntEnumArgument("gamemode", $gamemode, $this->gamemodes),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getGamemode(): IntEnumArgument {
        return $this->gamemode;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $gamemode = GameModeIdMap::getInstance()->fromId($this->gamemode->getValue());

        yield Await::ALL;
        return $player->getGamemode() === $gamemode;
    }
}
