<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;

class SendActionBarMessage extends TypePlayerMessage {

    protected string $id = self::SEND_ACTION_BAR_MESSAGE;

    protected string $name = "action.sendActionBarMessage.name";
    protected string $detail = "action.sendActionBarMessage.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendActionBarMessage($message);
        yield true;
    }
}