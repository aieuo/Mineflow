<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;
use function count;

class OnlinePlayerLessThan extends OnlinePlayerCount {

    public function __construct(string $value = "") {
        parent::__construct(self::ONLINE_PLAYER_LESS_THAN, value: $value);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value = $this->getValue()->getInt($source);

        yield Await::ALL;
        return count(Server::getInstance()->getOnlinePlayers()) < $value;
    }
}