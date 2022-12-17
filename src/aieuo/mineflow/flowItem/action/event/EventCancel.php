<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\event;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\Language;
use pocketmine\event\Cancellable;
use SOFe\AwaitGenerator\Await;

class EventCancel extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct() {
        parent::__construct(self::EVENT_CANCEL, FlowItemCategory::EVENT);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $event = $source->getEvent();
        if (!($event instanceof Cancellable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.eventCancel.notCancelable"));
        }
        $event->cancel();

        yield Await::ALL;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
    }

    public function loadSaveData(array $content): FlowItem {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}
