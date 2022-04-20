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
use function array_key_last;
use function explode;

class EventTrigger extends Trigger {

    private bool $enabled = true;
    private string $eventClass;

    public static function fromEventClass(string $class, string $subKey = ""): EventTrigger {
        $names = explode("\\", $class);
        $key = $names[array_key_last($names)];
        return self::create($key, $subKey);
    }

    public static function create(string $key, string $subKey = ""): EventTrigger {
        return Main::getEventManager()->getTrigger($key) ?? new EventTrigger($key, $subKey, "");
    }

    public function __construct(string $key, string $subKey, string $eventClass = null) {
        if ($eventClass === null) {
            $eventClass = $key;
            $names = explode("\\", $key);
            $key = $names[array_key_last($names)];
        }
        $this->eventClass = $eventClass;

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

    public function filter(Event $event): bool  {
        return true;
    }

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function getEventClass(): string {
        return $this->eventClass;
    }

    public function __toString(): string {
        return Language::exists("trigger.event.".$this->getKey()) ? Language::get("trigger.event.".$this->getKey()) : $this->getKey();
    }
}