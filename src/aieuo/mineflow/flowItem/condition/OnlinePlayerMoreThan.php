<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use function count;

class OnlinePlayerMoreThan extends OnlinePlayerLessThan {

    protected string $id = self::ONLINE_PLAYER_MORE_THAN;

    protected string $name = "condition.onlinePlayerMoreThan.name";
    protected string $detail = "condition.onlinePlayerMoreThan.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value = $this->getInt($source->replaceVariables($this->getValue()));

        yield true;
        return count(Server::getInstance()->getOnlinePlayers()) > $value;
    }
}