<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class SendActionBarMessage extends TypePlayerMessage {

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::SEND_ACTION_BAR_MESSAGE, player: $player, message: $message);
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($this->getMessage()->getString($source));

        $player = $this->getPlayer()->getOnlinePlayer($source);
        $player->sendActionBarMessage($message);
        yield Await::ALL;
    }
}