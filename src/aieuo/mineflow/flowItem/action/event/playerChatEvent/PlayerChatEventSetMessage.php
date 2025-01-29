<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\event\playerChatEvent;

use aieuo\mineflow\flowItem\argument\EventArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\trigger\event\PlayerChatEventTrigger;
use pocketmine\event\player\PlayerChatEvent;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class PlayerChatEventSetMessage extends SimpleAction {

    public function __construct(string $event = "event", string $message = "") {
        parent::__construct(self::PLAYER_CHAT_EVENT_SET_MESSAGE, FlowItemCategory::PLAYER_CHAT_EVENT);

        $this->setArguments([
            EventArgument::create("event", $event),
            StringArgument::create("message", $message, "@action.message.form.message")->optional()->example("aieuo"),
        ]);
    }

    public function getEvent(): EventArgument {
        return $this->getArgument("event");
    }

    public function getMessage(): StringArgument {
        return $this->getArgument("message");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $event = $this->getEvent()->getEvent($source);
        $message = $this->getMessage()->getString($source);

        if (!($event instanceof PlayerChatEvent)) {
            throw $this->getEvent()->createTypeMismatchedException((string)new PlayerChatEventTrigger());
        }

        $event->setMessage($message);

        yield Await::ALL;
    }
}