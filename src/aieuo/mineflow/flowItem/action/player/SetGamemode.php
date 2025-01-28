<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\IntEnumArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\data\java\GameModeIdMap;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

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
            PlayerArgument::create("player", $player),
            IntEnumArgument::create("gamemode", $gamemode)->options($this->gamemodes),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getGamemode(): IntEnumArgument {
        return $this->getArgument("gamemode");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $gamemode = $this->getGamemode()->getEnumValue();
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->setGamemode(GameModeIdMap::getInstance()->fromId($gamemode));

        yield Await::ALL;
    }
}