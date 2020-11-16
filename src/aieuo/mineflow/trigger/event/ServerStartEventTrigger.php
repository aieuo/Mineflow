<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\event\EntityAttackEvent;
use aieuo\mineflow\event\ServerStartEvent;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;

class ServerStartEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(ServerStartEvent::class, $subKey);
    }
}