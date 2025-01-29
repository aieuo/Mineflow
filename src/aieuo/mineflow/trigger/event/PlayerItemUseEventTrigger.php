<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\Vector3Variable;
use pocketmine\event\player\PlayerItemUseEvent;

class PlayerItemUseEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerItemUseEvent", PlayerItemUseEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerItemUseEvent $event */
        $target = $event->getPlayer();
        $item = $event->getItem();
        $direction = $event->getDirectionVector();
        return array_merge(DefaultVariables::getPlayerVariables($target), [
            "item" => new ItemVariable($item),
            "direction" => new Vector3Variable($direction),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "item" => new DummyVariable(ItemVariable::class),
            "direction" => new DummyVariable(Vector3Variable::class),
        ];
    }
}
