<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\command;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class Command extends SimpleAction {

    private PlayerArgument $player;
    private StringArgument $command;

    public function __construct(string $player = "", string $command = "") {
        parent::__construct(self::COMMAND, FlowItemCategory::COMMAND);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->command = new StringArgument("command", $command, example: "command"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getCommand(): StringArgument {
        return $this->command;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $command = $this->command->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        Server::getInstance()->dispatchCommand($player, $command);

        yield Await::ALL;
    }
}
