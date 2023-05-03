<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;

class SendJukeboxPopup extends TypePlayerMessage {

    protected string $id = self::SEND_JUKEBOX_POPUP;

    protected string $name = "action.sendJukeboxPopup.name";
    protected string $detail = "action.sendJukeboxPopup.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendJukeboxPopup($message, []);
        yield true;
    }
}