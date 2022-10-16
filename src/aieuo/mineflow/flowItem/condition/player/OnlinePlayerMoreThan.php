<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;
use function count;

class OnlinePlayerMoreThan extends OnlinePlayerCount {

    public function __construct(string $value = "") {
        parent::__construct(self::ONLINE_PLAYER_MORE_THAN, value: $value);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value = $source->replaceVariables($this->getValue());
        $this->throwIfInvalidNumber($value);

        yield Await::ALL;
        return count(Server::getInstance()->getOnlinePlayers()) > $value;
    }
}