<?php
declare(strict_types=1);

namespace aieuo\mineflow\event;

use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\variable\DefaultVariables;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerEvent;
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\event\Listener;
use pocketmine\event\EventPriority;
use pocketmine\event\Event;
use pocketmine\Server;

class EventTriggerListener implements Listener {

    public function registerEvents(): void {
        foreach (Main::getEventManager()->getEnabledEvents() as $event => $value) {
            if (!class_exists($event)) continue;

            $this->registerEvent($event, "onEvent");
        }
    }

    private function registerEvent(string $event, string $method): void {
        $pluginManager = Server::getInstance()->getPluginManager();
        $pluginManager->registerEvent($event, $this, EventPriority::NORMAL, new MethodEventExecutor($method), Main::getInstance());
    }

    public function onEvent(Event $event): void {
        $eventName = $event->getEventName();

        $holder = TriggerHolder::getInstance();
        if ($holder->existsRecipeByString(Trigger::TYPE_EVENT, $eventName)) {
            $recipes = $holder->getRecipes(new Trigger(Trigger::TYPE_EVENT, $eventName));
            $target = null;
            if ($event instanceof PlayerEvent or $event instanceof BlockEvent or $event instanceof CraftItemEvent) {
                $target = $event->getPlayer();
            } elseif ($event instanceof EntityDamageByEntityEvent) {
                $target = $event->getDamager();
            } elseif ($event instanceof EntityEvent) {
                $target = $event->getEntity();
            }
            $variables = DefaultVariables::getEventVariables($event, $eventName);
            $recipes->executeAll($target, $variables, $event);
        }
    }
}