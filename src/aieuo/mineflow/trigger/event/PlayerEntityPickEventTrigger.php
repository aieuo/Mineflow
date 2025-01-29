<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerEntityPickEvent;

class PlayerEntityPickEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerEntityPickEvent", PlayerEntityPickEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerEntityPickEvent $event */
        $target = $event->getPlayer();
        $entity = $event->getEntity();
        $item = $event->getResultItem();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getEntityVariables($entity, "entity"), [
            "item" => new ItemVariable($item),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "entity" => new DummyVariable(EntityVariable::class),
            "item" => new DummyVariable(ItemVariable::class),
        ];
    }
}
