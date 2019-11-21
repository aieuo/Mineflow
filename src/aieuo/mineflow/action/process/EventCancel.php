<?php

namespace aieuo\mineflow\action\process;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class EventCancel extends Process {

    protected $id = self::EVENT_CANCEL;

    protected $name = "@action.eventCancel.name";
    protected $description = "@action.eventCancel.description";
    protected $detail = "@action.eventCancel.detail";

    protected $category = Categories::CATEGORY_ACTION_COMMON;

    protected $targetRequired = Recipe::TARGET_NONE;

    /** @var Event */
    private $event;

    public function getEvent(): ?Event {
        return $this->event;
    }

    public function setEvent(?Event $event) {
        $this->event = $event;
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        $event = $this->getEvent();
        if ($event instanceof Cancellable) $event->setCancelled();
        return true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function parseFromSaveData(array $content): ?Process {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}