<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\event\Listener;
use pocketmine\event\RegisteredListener;
use pocketmine\Server;
use function get_parent_class;

class EventTriggerListener implements Listener {

    /** @var array<string, RegisteredListener> */
    private array $registeredListeners = [];

    public function registerEvent(string $event): void {
        if (isset($this->registeredListeners[$event])) return;

        $pluginManager = Server::getInstance()->getPluginManager();
        /** @noinspection PhpUnhandledExceptionInspection */
        $registeredListener = $pluginManager->registerEvent($event, \Closure::fromCallable([$this, "onEvent"]), EventPriority::NORMAL, Main::getInstance(), true);
        $this->registeredListeners[$event] = $registeredListener;
    }

    public function unregisterEvent(string $event): void {
        if (!isset($this->registeredListeners[$event])) return;

        HandlerListManager::global()->unregisterAll($this->registeredListeners[$event]);
        unset($this->registeredListeners[$event]);
    }

    public function getRegisteredListener(string $event): ?RegisteredListener {
        return $this->registeredListeners[$event] ?? null;
    }

    public function onEvent(Event $event): void {
        $manager = Mineflow::getEventManager();
        $class = $event::class;
        do {
            $keys = $manager->getEventNamesFromClass($class);
            foreach ($keys as $key) {
                $trigger = $manager->getTrigger($key);
                if ($trigger === null or !$manager->isTriggerEnabled($trigger) or !$trigger->filter($event)) continue;

                $variables = $trigger->getVariables($event);
                TriggerHolder::executeRecipeAll($trigger, $trigger->getTargetEntity($event), $variables, $event);
            }
        } while (($class = get_parent_class($class)) !== false);
    }
}