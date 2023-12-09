<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\command;

use aieuo\mineflow\command\MineflowConsoleCommandSender;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class CommandConsole extends SimpleAction {

    public function __construct(string $command = "") {
        parent::__construct(self::COMMAND_CONSOLE, FlowItemCategory::COMMAND, [FlowItemPermission::CONSOLE]);

        $this->setArguments([
            StringArgument::create("command", $command, "@action.command.form.command")->example("mineflow"),
        ]);
    }

    public function getCommand(): StringArgument {
        return $this->getArguments()[0];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $command = $this->getCommand()->getString($source);

        Server::getInstance()->dispatchCommand(new MineflowConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $command);

        yield Await::ALL;
    }
}
