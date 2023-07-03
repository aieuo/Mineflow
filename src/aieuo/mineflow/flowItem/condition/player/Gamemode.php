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

    public function __construct(string $player = "", int $gamemode = 0) {
        parent::__construct(self::GAMEMODE, FlowItemCategory::PLAYER);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new IntEnumArgument("gamemode", $gamemode, $this->gamemodes),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getGamemode(): IntEnumArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);
        $gamemode = GameModeIdMap::getInstance()->fromId($this->getGamemode()->getValue());

        yield Await::ALL;
        return $player->getGamemode() === $gamemode;
    }
}
