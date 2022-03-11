<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use function count;

class OnlinePlayerMoreThan extends OnlinePlayerCount {

    protected string $name = "condition.onlinePlayerMoreThan.name";
    protected string $detail = "condition.onlinePlayerMoreThan.detail";

    public function __construct(string $value = "") {
        parent::__construct(self::ONLINE_PLAYER_MORE_THAN, value: $value);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value = $source->replaceVariables($this->getValue());
        $this->throwIfInvalidNumber($value);

        yield true;
        return count(Server::getInstance()->getOnlinePlayers()) > $value;
    }
}