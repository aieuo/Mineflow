<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class BroadcastMessage extends TypeMessage {

    public function __construct(string $message = "") {
        parent::__construct(self::BROADCAST_MESSAGE, message: $message);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));
        Server::getInstance()->broadcastMessage($message);

        yield Await::ALL;
    }
}
