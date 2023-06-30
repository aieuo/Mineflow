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
use SOFe\AwaitGenerator\Await;

class PlayerChatEventSetMessage extends SimpleAction {

    private EventArgument $event;
    private StringArgument $message;

    public function __construct(string $event = "event", string $message = "") {
        parent::__construct(self::PLAYER_CHAT_EVENT_SET_MESSAGE, FlowItemCategory::PLAYER_CHAT_EVENT);

        $this->setArguments([
            $this->event = new EventArgument("event", $event),
            $this->message = new StringArgument("message", $message, "@action.message.form.message", example: "aieuo", optional: true),
        ]);
    }

    public function getEvent(): EventArgument {
        return $this->event;
    }

    public function getMessage(): StringArgument {
        return $this->message;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $event = $this->event->getEvent($source);
        $message = $this->message->getString($source);

        if (!($event instanceof PlayerChatEvent)) {
            throw $this->event->createTypeMismatchedException((string)new PlayerChatEventTrigger());
        }

        $event->setMessage($message);

        yield Await::ALL;
    }
}
