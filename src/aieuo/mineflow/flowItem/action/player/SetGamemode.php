<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\IntEnumArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\data\java\GameModeIdMap;
use SOFe\AwaitGenerator\Await;

class SetGamemode extends SimpleAction {

    private array $gamemodes = [
        "action.gamemode.survival",
        "action.gamemode.creative",
        "action.gamemode.adventure",
        "action.gamemode.spectator"
    ];

    public function __construct(string $player = "", int $gamemode = 0) {
        parent::__construct(self::SET_GAMEMODE, FlowItemCategory::PLAYER);

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
        $gamemode = $this->getGamemode()->getValue();
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->setGamemode(GameModeIdMap::getInstance()->fromId($gamemode));

        yield Await::ALL;
    }
}
