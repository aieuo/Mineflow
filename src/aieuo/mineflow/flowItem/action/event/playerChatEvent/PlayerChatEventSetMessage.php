<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\event\playerChatEvent;

use aieuo\mineflow\flowItem\argument\EventArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\trigger\event\PlayerChatEventTrigger;
use pocketmine\event\player\PlayerChatEvent;
use SOFe\AwaitGenerator\Await;

class PlayerChatEventSetMessage extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EventArgument $event;
    private StringArgument $message;

    public function __construct(string $event = "event", string $message = "") {
        parent::__construct(self::PLAYER_CHAT_EVENT_SET_MESSAGE, FlowItemCategory::PLAYER_CHAT_EVENT);

        $this->event = new EventArgument("event", $event);
        $this->message = new StringArgument("message", $message, "@action.message.form.message", example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["event", "message"];
    }

    public function getDetailReplaces(): array {
        return [$this->event->get(), $this->message->get()];
    }

    public function getEvent(): EventArgument {
        return $this->event;
    }

    public function getMessage(): StringArgument {
        return $this->message;
    }

    public function isDataValid(): bool {
        return $this->event->isValid();
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

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->event->createFormElement($variables),
            $this->message->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->event->set($content[0]);
        $this->message->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->event->get(), $this->message->get()];
    }
}
