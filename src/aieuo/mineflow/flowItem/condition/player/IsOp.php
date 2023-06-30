<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class IsOp extends SimpleCondition {

    private PlayerArgument $player;

    public function __construct(string $player = "") {
        parent::__construct(self::IS_OP, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);

        yield Await::ALL;
        return Server::getInstance()->isOp($player->getName());
    }
}
