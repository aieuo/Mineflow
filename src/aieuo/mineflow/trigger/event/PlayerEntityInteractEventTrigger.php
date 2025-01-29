<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\Vector3Variable;
use pocketmine\event\player\PlayerEntityInteractEvent;

class PlayerEntityInteractEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerEntityInteractEvent", PlayerEntityInteractEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerEntityInteractEvent $event */
        $target = $event->getPlayer();
        $entity = $event->getEntity();
        $clickPos = $event->getClickPosition();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getEntityVariables($entity, "entity"), [
            "clickPos" => new Vector3Variable($clickPos),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "entity" => new DummyVariable(EntityVariable::class),
            "clickPos" => new DummyVariable(Vector3Variable::class),
        ];
    }
}