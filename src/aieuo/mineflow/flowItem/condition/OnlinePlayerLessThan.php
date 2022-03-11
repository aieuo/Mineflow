<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use function count;

class OnlinePlayerLessThan extends OnlinePlayerCount {

    protected string $name = "condition.onlinePlayerLessThan.name";
    protected string $detail = "condition.onlinePlayerLessThan.detail";

    public function __construct(string $value = "") {
        parent::__construct(self::ONLINE_PLAYER_LESS_THAN, value: $value);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value = $source->replaceVariables($this->getValue());
        $this->throwIfInvalidNumber($value);

        yield true;
        return count(Server::getInstance()->getOnlinePlayers()) < $value;
    }
}
