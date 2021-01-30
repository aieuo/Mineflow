<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;

class SendMessageToConsole extends TypeMessage {

    protected $id = self::SEND_MESSAGE_TO_CONSOLE;

    protected $category = Category::COMMON;

    protected $name = "action.sendMessageToConsole.name";
    protected $detail = "action.sendMessageToConsole.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $message = $origin->replaceVariables($this->getMessage());
        Main::getInstance()->getLogger()->info($message);
        yield true;
    }
}