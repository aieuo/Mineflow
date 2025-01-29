<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\Mineflow;
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

    public static function fromEventClass(string $class): EventTrigger {
        $names = explode("\\", $class);
        $name = $names[array_key_last($names)];
        return self::get($name) ?? new EventTrigger($name, $class);
    }

    public static function get(string $eventName): ?EventTrigger {
        return Mineflow::getEventManager()->getTrigger($eventName);
    }

    public function __construct(private string $eventName, private string $eventClass) {
        parent::__construct(Triggers::EVENT);
    }

    public function getEventName(): string {
        return $this->eventName;
    }

    public function getEventClass(): string {
        return $this->eventClass;
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

    public function hash(): string|int {
        return $this->eventName;
    }

    public function __toString(): string {
        return Language::exists("trigger.event.".$this->getEventName()) ? Language::get("trigger.event.".$this->getEventName()) : $this->getEventName();
    }
}