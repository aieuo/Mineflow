<?php
declare(strict_types=1);

namespace aieuo\mineflow\event;

use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\trigger\TriggerTypes;
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
        if ($holder->existsRecipeByString(TriggerTypes::EVENT, $eventName)) {
            $trigger = EventTrigger::create($eventName);
            $recipes = $holder->getRecipes($trigger);
            $variables = $trigger->getVariables($event);
            $recipes->executeAll($trigger->getTargetEntity($event), $variables, $event);
        }
    }
}