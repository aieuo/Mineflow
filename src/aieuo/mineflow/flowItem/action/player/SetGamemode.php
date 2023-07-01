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

    private PlayerArgument $player;
    private IntEnumArgument $gamemode;

    public function __construct(string $player = "", int $gamemode = 0) {
        parent::__construct(self::SET_GAMEMODE, FlowItemCategory::PLAYER);

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
        $gamemode = $this->gamemode->getValue();
        $player = $this->player->getOnlinePlayer($source);

        $player->setGamemode(GameModeIdMap::getInstance()->fromId($gamemode));

        yield Await::ALL;
    }
}
