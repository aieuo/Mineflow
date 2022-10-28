<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\trigger\Triggers;
use pocketmine\event\Listener;
use pocketmine\event\EventPriority;
use pocketmine\event\Event;
use pocketmine\Server;
use function get_parent_class;
use function in_array;

class EventTriggerListener implements Listener {

    /** @var string[] */
    private array $registeredEvents = [];

    public function registerEvent(string $event): void {
        if (in_array($event, $this->registeredEvents, true)) return;

        $pluginManager = Server::getInstance()->getPluginManager();
        /** @noinspection PhpUnhandledExceptionInspection */
        $pluginManager->registerEvent($event, \Closure::fromCallable([$this, "onEvent"]), EventPriority::NORMAL, Main::getInstance(), true);
        $this->registeredEvents[] = $event;
    }

    public function onEvent(Event $event): void {
        $holder = TriggerHolder::getInstance();
        $manager = Mineflow::getEventManager();
        $class = $event::class;
        do {
            $keys = $manager->getKeysFromEventClass($class);
            foreach ($keys as $key) {
                $trigger = $manager->getTrigger($key);
                if ($trigger === null or !$trigger->filter($event)) continue;

                if ($holder->existsRecipeByString(Triggers::EVENT, $key)) {
                    $recipes = $holder->getRecipes($trigger);
                    $variables = $trigger->getVariables($event);
                    $recipes?->executeAll($trigger->getTargetEntity($event), $variables, $event);
                }
            }
        } while (($class = get_parent_class($class)) !== false);
    }
}
