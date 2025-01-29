<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\event\ServerStartEvent;

class ServerStartEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("ServerStartEvent", ServerStartEvent::class);
    }
}