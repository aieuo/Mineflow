<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;

class BroadcastMessage extends TypeMessage {

    protected string $id = self::BROADCAST_MESSAGE;

    protected string $name = "action.broadcastMessage.name";
    protected string $detail = "action.broadcastMessage.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());
        Server::getInstance()->broadcastMessage($message);
        yield true;
    }
}