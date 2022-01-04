<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\event\player\PlayerDropItemEvent;

class PlayerDropItemEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("PlayerDropItemEvent", $subKey, PlayerDropItemEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerDropItemEvent $event */
        $target = $event->getPlayer();
        $item = $event->getItem();
        return array_merge(DefaultVariables::getPlayerVariables($target), [
            "item" => new ItemObjectVariable($item),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "item" => new DummyVariable(DummyVariable::ITEM),
        ];
    }
}