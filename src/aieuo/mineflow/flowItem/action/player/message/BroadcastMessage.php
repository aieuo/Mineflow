<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class BroadcastMessage extends TypeMessage {

    public function __construct(string $message = "") {
        parent::__construct(self::BROADCAST_MESSAGE, message: $message);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($this->getMessage()->getString($source));
        Server::getInstance()->broadcastMessage($message);

        yield Await::ALL;
    }
}