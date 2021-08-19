<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\action\player\TypeMessage;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;

class SendMessageToConsole extends TypeMessage {

    protected string $id = self::SEND_MESSAGE_TO_CONSOLE;

    protected string $category = Category::COMMON;

    protected string $name = "action.sendMessageToConsole.name";
    protected string $detail = "action.sendMessageToConsole.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());
        Main::getInstance()->getLogger()->info($message);
        yield true;
    }
}