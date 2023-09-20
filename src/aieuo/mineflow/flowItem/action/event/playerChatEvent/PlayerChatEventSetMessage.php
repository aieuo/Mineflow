<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\event\playerChatEvent;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EventFlowItem;
use aieuo\mineflow\flowItem\base\EventFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\EventVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\trigger\event\PlayerChatEventTrigger;
use pocketmine\event\player\PlayerChatEvent;
use SOFe\AwaitGenerator\Await;

class PlayerChatEventSetMessage extends FlowItem implements EventFlowItem {
    use EventFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(private string $event = "event", private string $message = "") {
        parent::__construct(self::PLAYER_CHAT_EVENT_SET_MESSAGE, FlowItemCategory::PLAYER_CHAT_EVENT);

        $this->setEventVariableName($event);
    }

    public function getDetailDefaultReplaces(): array {
        return ["event", "message"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEventVariableName(), $this->getMessage()];
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function isDataValid(): bool {
        return $this->getEventVariableName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $event = $this->getEvent($source);
        $message = $source->replaceVariables($this->getMessage());

        if (!($event instanceof PlayerChatEvent)) {
            throw $this->createTypeMismatchedException($this->getEventVariableName(), (string)new PlayerChatEventTrigger());
        }

        $event->setMessage($message);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new EventVariableDropdown($variables, $this->getEventVariableName()),
            new ExampleInput("@action.message.form.message", "aieuo", $this->getMessage()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setEventVariableName($content[0]);
        $this->setMessage($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getEventVariableName(), $this->getMessage()];
    }
}