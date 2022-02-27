<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\event;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\event\Cancellable;

class EventCancel extends FlowItem {

    protected string $id = self::EVENT_CANCEL;

    protected string $name = "action.eventCancel.name";
    protected string $detail = "action.eventCancel.detail";

    protected string $category = FlowItemCategory::EVENT;

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $event = $source->getEvent();
        if (!($event instanceof Cancellable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.eventCancel.notCancelable"));
        }
        $event->cancel();
        yield true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): FlowItem {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}