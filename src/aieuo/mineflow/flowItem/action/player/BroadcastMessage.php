<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;

class BroadcastMessage extends TypeMessage {

    protected string $name = "action.broadcastMessage.name";
    protected string $detail = "action.broadcastMessage.detail";

    public function __construct(string $message = "") {
        parent::__construct(self::BROADCAST_MESSAGE, message: $message);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));
        Server::getInstance()->broadcastMessage($message);
        yield true;
    }
}