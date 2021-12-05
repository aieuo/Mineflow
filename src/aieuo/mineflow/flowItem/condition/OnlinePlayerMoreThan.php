<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;
use function count;

class OnlinePlayerMoreThan extends OnlinePlayerLessThan {

    protected string $id = self::ONLINE_PLAYER_MORE_THAN;

    protected string $name = "condition.onlinePlayerMoreThan.name";
    protected string $detail = "condition.onlinePlayerMoreThan.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value = $source->replaceVariables($this->getValue());
        $this->throwIfInvalidNumber($value);

        yield true;
        return count(Server::getInstance()->getOnlinePlayers()) > $value;
    }
}