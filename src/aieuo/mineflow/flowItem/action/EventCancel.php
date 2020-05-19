<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\utils\Language;
use pocketmine\event\Cancellable;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;

class EventCancel extends Action {

    protected $id = self::EVENT_CANCEL;

    protected $name = "action.eventCancel.name";
    protected $detail = "action.eventCancel.detail";

    protected $category = Category::EVENT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $event = $origin->getEvent();
        if (!($event instanceof Cancellable)) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.eventCancel.notCancelable"]]));
        }
        $event->setCancelled();
        return true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): Action {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}