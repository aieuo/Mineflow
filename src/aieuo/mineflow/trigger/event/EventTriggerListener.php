<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\trigger\Triggers;
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\event\Listener;
use pocketmine\event\EventPriority;
use pocketmine\event\Event;
use pocketmine\Server;

class EventTriggerListener implements Listener {

    /** @var string[] */
    private array $registeredEvents = [];

    public function registerEvent(string $event): void {
        if (in_array($event, $this->registeredEvents, true)) return;

        $pluginManager = Server::getInstance()->getPluginManager();
        $pluginManager->registerEvent($event, $this, EventPriority::NORMAL, new MethodEventExecutor("onEvent"), Main::getInstance());
        $this->registeredEvents[] = $event;
    }

    public function onEvent(Event $event): void {
        $eventName = $event->getEventName();

        $holder = TriggerHolder::getInstance();
        if ($holder->existsRecipeByString(Triggers::EVENT, $eventName)) {
            $trigger = EventTrigger::create($eventName);
            $recipes = $holder->getRecipes($trigger);
            $variables = $trigger->getVariables($event);
            $recipes->executeAll($trigger->getTargetEntity($event), $variables, $event);
        }
    }
}