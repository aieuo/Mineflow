<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;
use function count;

class OnlinePlayerMoreThan extends OnlinePlayerCount {

    public function __construct(string $value = "") {
        parent::__construct(self::ONLINE_PLAYER_MORE_THAN, value: $value);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value = $this->getValue()->getInt($source);

        yield Await::ALL;
        return count(Server::getInstance()->getOnlinePlayers()) > $value;
    }
}