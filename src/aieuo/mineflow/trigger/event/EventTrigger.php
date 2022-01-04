<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

class EventTrigger extends Trigger {

    private bool $enabled = true;

    public static function create(string $eventName, string $subKey = ""): EventTrigger {
        return Main::getEventManager()->getTrigger($eventName) ?? new EventTrigger($eventName, $subKey);
    }

    public function __construct(string $key, string $subKey = "") {
        parent::__construct(Triggers::EVENT, $key, $subKey);
    }

    public function getTargetEntity(Event $event): ?Entity {
        if ($event instanceof PlayerEvent or $event instanceof CraftItemEvent) {
            $target = $event->getPlayer();
        } elseif ($event instanceof EntityDamageByEntityEvent) {
            $target = $event->getDamager();
        } elseif ($event instanceof EntityEvent) {
            $target = $event->getEntity();
        } else {
            $target = null;
        }
        return $target;
    }

    /**
     * @param Event $event
     * @return array<string, Variable>
     */
    public function getVariables(mixed $event): array {
        $target = $this->getTargetEntity($event);
        if ($target === null) return [];
        return DefaultVariables::getEntityVariables($this->getTargetEntity($event));
    }

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function __toString(): string {
        return Language::exists("trigger.event.".$this->getKey()) ? Language::get("trigger.event.".$this->getKey()) : $this->getKey();
    }
}