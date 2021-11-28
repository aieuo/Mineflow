<?php

namespace aieuo\mineflow\trigger\event;


use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\event\Event;
use pocketmine\utils\Config;

class EventManager {

    private Config $setting;

    /** @var EventTrigger[][] */
    private array $triggers = [];
    /** @var array<string, bool> */
    private array $events = [];
    /** @var array<class-string<Event>, string[]> */
    private array $keys = [];
    private EventTriggerListener $eventListener;

    public function __construct(Config $setting) {
        $this->setting = $setting;
        $this->eventListener = new EventTriggerListener();

        $this->addDefaultTriggers();
    }

    public function addDefaultTriggers(): void {
        $this->addTrigger(new BlockBreakEventTrigger(), true);
        $this->addTrigger(new BlockPlaceEventTrigger(), true);
        $this->addTrigger(new CraftItemEventTrigger(), true);
        $this->addTrigger(new EntityAttackEventTrigger(), true);
        $this->addTrigger(new EntityDamageEventTrigger(), true);
        $this->addTrigger(new EntityLevelChangeEventTrigger(), true);
        $this->addTrigger(new FurnaceBurnEventTrigger(), false);
        $this->addTrigger(new LevelLoadEventTrigger(), false);
        $this->addTrigger(new PlayerBedEnterEventTrigger(), false);
        $this->addTrigger(new PlayerChatEventTrigger(), true);
        $this->addTrigger(new PlayerCommandPreprocessEventTrigger(), true);
        $this->addTrigger(new PlayerDeathEventTrigger(), true);
        $this->addTrigger(new EntityDeathEventTrigger(), false);
        $this->addTrigger(new PlayerDropItemEventTrigger(), false);
        $this->addTrigger(new PlayerExhaustEventTrigger(), false);
        $this->addTrigger(new PlayerInteractEventTrigger(), true);
        $this->addTrigger(new PlayerItemConsumeEventTrigger(), true);
        $this->addTrigger(new PlayerJoinEventTrigger(), true);
        $this->addTrigger(new PlayerJumpEventTrigger(), true);
        $this->addTrigger(new PlayerMoveEventTrigger(), false);
        $this->addTrigger(new PlayerQuitEventTrigger(), true);
        $this->addTrigger(new PlayerToggleFlightEventTrigger(), true);
        $this->addTrigger(new PlayerToggleSneakEventTrigger(), true);
        $this->addTrigger(new PlayerToggleSprintEventTrigger(), false);
        $this->addTrigger(new ProjectileHitEntityEventTrigger(), false);
        $this->addTrigger(new SignChangeEventTrigger(), false);
        $this->addTrigger(new ServerStartEventTrigger(), true);
        $this->addTrigger(new InventoryPickupItemEventTrigger(), false);
        $this->addTrigger(new PlayerChangeSkinEventTrigger(), false);
    }

    public function addTrigger(EventTrigger $trigger, bool $defaultEnabled): void {
        $key = $trigger->getKey();
        $eventClass = $trigger->getEventClass();
        $trigger->setEnabled((bool)$this->setting->get($key, $this->setting->get($eventClass, $defaultEnabled)));

        if ($trigger->isEnabled()) {
            $this->eventListener->registerEvent($eventClass);
        }

        $this->triggers[$key][$trigger->getSubKey()] = $trigger;
        $this->events[$key] = $trigger->isEnabled();
        $this->keys[$eventClass][] = $key;
    }

    public function getKeysFromEventClass(string $class): array {
        return $this->keys[$class] ?? [];
    }

    public function getTrigger(string $key, string $subKey = ""): ?EventTrigger {
        return $this->triggers[$key][$subKey] ?? null;
    }

    public function existsTrigger(string $key, string $subKey = ""): bool {
        return isset($this->triggers[$key][$subKey]);
    }

    public function getAll(): array {
        return $this->triggers;
    }

    public function getEvents(): array {
        return $this->events;
    }

    public function getEnabledEvents(): array {
        return array_filter($this->events, fn(bool $v) => $v);
    }

    public function enableEvent(string $event): void {
        $this->setting->set($event, true);
        $this->setting->save();

        $this->events[$event] = true;
    }

    public function disableEvent(string $event): void {
        $this->setting->set($event, false);
        $this->setting->save();

        $this->events[$event] = false;
    }

    public function getAssignedRecipes(string $event): array {
        $recipes = [];
        $containers = TriggerHolder::getInstance()->getRecipesWithSubKey(EventTrigger::create($event));
        foreach ($containers as $name => $container) {
            foreach ($container->getAllRecipe() as $recipe) {
                $path = $recipe->getGroup()."/".$recipe->getName();
                if (!isset($recipes[$path])) $recipes[$path] = [];
                $recipes[$path][] = $name;
            }
        }
        return $recipes;
    }
}