<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\command;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;

class Command extends SimpleAction {

    public function __construct(string $player = "", string $command = "") {
        parent::__construct(self::COMMAND, FlowItemCategory::COMMAND);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("command", $command)->example("command"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getCommand(): StringArgument {
        return $this->getArgument("command");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $command = $this->getCommand()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        Server::getInstance()->dispatchCommand($player, $command);

        yield Await::ALL;
    }
}