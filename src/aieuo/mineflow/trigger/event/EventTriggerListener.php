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
use function get_parent_class;
use function in_array;

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
        $holder = TriggerHolder::getInstance();
        $class = $event::class;
        do {
            if ($holder->existsRecipeByString(Triggers::EVENT, $class)) {
                $trigger = EventTrigger::create($class);
                $recipes = $holder->getRecipes($trigger);
                $variables = $trigger->getVariables($event);
                $recipes?->executeAll($trigger->getTargetEntity($event), $variables, $event);
            }
        } while (($class = get_parent_class($class)) !== false);
    }
}