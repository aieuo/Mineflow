<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\event\ServerStartEvent;

class ServerStartEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("ServerStartEvent", $subKey, ServerStartEvent::class);
    }
}