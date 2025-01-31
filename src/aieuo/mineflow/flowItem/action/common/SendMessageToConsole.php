<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\action\player\message\TypeMessage;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class SendMessageToConsole extends TypeMessage {

    public function __construct(string $message = "") {
        parent::__construct(self::SEND_MESSAGE_TO_CONSOLE, FlowItemCategory::COMMON, $message);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($this->getMessage()->getString($source));
        Main::getInstance()->getLogger()->info($message);

        yield Await::ALL;
    }
}