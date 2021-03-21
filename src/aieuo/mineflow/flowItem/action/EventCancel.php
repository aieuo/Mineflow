<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\event\Cancellable;

class EventCancel extends FlowItem {

    protected $id = self::EVENT_CANCEL;

    protected $name = "action.eventCancel.name";
    protected $detail = "action.eventCancel.detail";

    protected $category = Category::EVENT;

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $event = $source->getEvent();
        if (!($event instanceof Cancellable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.eventCancel.notCancelable"));
        }
        $event->setCancelled();
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