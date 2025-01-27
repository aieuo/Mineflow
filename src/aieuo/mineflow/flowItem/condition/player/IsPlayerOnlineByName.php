<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\player\Player;
use pocketmine\Server;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;

class IsPlayerOnlineByName extends SimpleCondition {

    public function __construct(string $playerName = "target") {
        parent::__construct(self::IS_PLAYER_ONLINE_BY_NAME, FlowItemCategory::PLAYER);

        $this->setArguments([
            StringArgument::create("name", $playerName, "@condition.isPlayerOnline.form.name")->example("target"),
        ]);
    }

    public function getPlayerName(): StringArgument {
        return $this->getArgument("name");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getPlayerName()->getString($source);

        $player = Server::getInstance()->getPlayerExact($name);

        yield Await::ALL;
        return $player instanceof Player;
    }
}