<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\action\player\TypeMessage;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;

class SendMessageToConsole extends TypeMessage {
    use ActionNameWithMineflowLanguage;

    public function __construct(string $message = "") {
        parent::__construct(self::SEND_MESSAGE_TO_CONSOLE, FlowItemCategory::COMMON, $message);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));
        Main::getInstance()->getLogger()->info($message);
        yield true;
    }
}