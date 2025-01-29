<?php
declare(strict_types=1);

namespace aieuo\mineflow\event;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use pocketmine\event\Event;

class CustomTriggerCallEvent extends Event {
    
    public function __construct(
        private CustomTrigger $trigger,
        private ?FlowItemExecutor $from,
    ) {
    }

    public function getTrigger(): CustomTrigger {
        return $this->trigger;
    }

    public function getFrom(): ?FlowItemExecutor {
        return $this->from;
    }
}