<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SendJukeboxPopup extends TypePlayerMessage {

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::SEND_JUKEBOX_POPUP, player: $player, message: $message);
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($source->replaceVariables($this->getMessage()));

        $player = $this->getOnlinePlayer($source);
        $player->sendJukeboxPopup($message, []);
        yield Await::ALL;
    }
}
