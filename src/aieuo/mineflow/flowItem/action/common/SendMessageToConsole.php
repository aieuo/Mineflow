<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\action\player\TypeMessage;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;

class SendMessageToConsole extends TypeMessage {

    protected string $id = self::SEND_MESSAGE_TO_CONSOLE;

    protected string $category = FlowItemCategory::COMMON;

    protected string $name = "action.sendMessageToConsole.name";
    protected string $detail = "action.sendMessageToConsole.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));
        Main::getInstance()->getLogger()->info($message);
        yield true;
    }
}