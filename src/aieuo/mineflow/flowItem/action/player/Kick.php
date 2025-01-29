<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class Kick extends SimpleAction {

    public function __construct(string $player = "", string $reason = "") {
        parent::__construct(self::KICK, FlowItemCategory::PLAYER);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("reason", $reason)->example("aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getReason(): StringArgument {
        return $this->getArgument("reason");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $reason = $this->getReason()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $reason): void {
            $player->kick($reason);
        }), 1);

        yield Await::ALL;
    }
}