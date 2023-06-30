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
use SOFe\AwaitGenerator\Await;

class Kick extends SimpleAction {

    private PlayerArgument $player;
    private StringArgument $reason;

    public function __construct(string $player = "", string $reason = "") {
        parent::__construct(self::KICK, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->reason = new StringArgument("reason", $reason, example: "aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getReason(): StringArgument {
        return $this->reason;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $reason = $this->reason->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $reason): void {
            $player->kick($reason);
        }), 1);

        yield Await::ALL;
    }
}
