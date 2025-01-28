<?php

namespace aieuo\mineflow\trigger\event;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\event\Event;
use pocketmine\utils\Config;

class EventManager {

    private Config $setting;

    /** @var EventTrigger[] */
    private array $triggers = [];
    /** @var array<string, bool> */
    private array $events = [];
    /** @var array<class-string<Event>, string[]> */
    private array $names = [];
    private EventTriggerListener $eventListener;

    public function __construct(Config $setting) {
        $this->setting = $setting;
        $this->eventListener = new EventTriggerListener();
    }

    public function getSetting(): Config {
        return $this->setting;
    }

    public function getEventListener(): EventTriggerListener {
        return $this->eventListener;
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
        $this->addTrigger(new PlayerEmoteEventTrigger(), false);
        $this->addTrigger(new PlayerExhaustEventTrigger(), false);
        $this->addTrigger(new PlayerGameModeChangeEventTrigger(), false);
        $this->addTrigger(new PlayerInteractEventTrigger(), true);
        $this->addTrigger(new PlayerItemConsumeEventTrigger(), true);
        $this->addTrigger(new PlayerJoinEventTrigger(), true);
        $this->addTrigger(new PlayerJumpEventTrigger(), true);
        $this->addTrigger(new PlayerMoveEventTrigger(), false);
        $this->addTrigger(new PlayerQuitEventTrigger(), true);
        $this->addTrigger(new PlayerToggleFlightEventTrigger(), true);
        $this->addTrigger(new PlayerToggleGlideEventTrigger(), false);
        $this->addTrigger(new PlayerToggleSneakEventTrigger(), true);
        $this->addTrigger(new PlayerToggleSprintEventTrigger(), false);
        $this->addTrigger(new PlayerToggleSwimEventTrigger(), false);
        $this->addTrigger(new ProjectileHitEntityEventTrigger(), false);
        $this->addTrigger(new SignChangeEventTrigger(), false);
        $this->addTrigger(new ServerStartEventTrigger(), true);
        $this->addTrigger(new InventoryPickupItemEventTrigger(), false);
        $this->addTrigger(new PlayerChangeSkinEventTrigger(), false);
        $this->addTrigger(new MineflowRecipeLoadEventTrigger(), true);
    }

    public function addTrigger(EventTrigger $trigger, bool $defaultEnabled): void {
        $eventName = $trigger->getEventName();
        $eventClass = $trigger->getEventClass();
        $enabled = (bool)$this->setting->get($eventName, $this->setting->get($eventClass, $defaultEnabled));

        if ($enabled) {
            $this->eventListener->registerEvent($eventClass);
        }

        $this->triggers[$eventName] = $trigger;
        $this->events[$eventName] = $enabled;
        $this->names[$eventClass][] = $eventName;
    }

    public function getEventNamesFromClass(string $class): array {
        return $this->names[$class] ?? [];
    }

    public function getTrigger(string $eventName): ?EventTrigger {
        return $this->triggers[$eventName] ?? null;
    }

    public function existsTrigger(string $eventName): bool {
        return isset($this->triggers[$eventName]);
    }

    public function getAll(): array {
        return $this->triggers;
    }

    public function getEvents(): array {
        return $this->events;
    }

    public function isTriggerEnabled(EventTrigger $trigger): bool {
        return $this->events[$trigger->getEventName()] ?? false;
    }

    public function setTriggerEnabled(EventTrigger $trigger): void {
        $this->events[$trigger->getEventName()] = true;

        $this->getEventListener()->registerEvent($trigger->getEventClass());
    }

    public function setTriggerDisabled(EventTrigger $trigger): void {
        $this->events[$trigger->getEventName()] = false;

        $count = 0;
        $keys = $this->getEventNamesFromClass($trigger->getEventClass());
        foreach ($keys as $key) {
            if ($trigger !== null and $this->isTriggerEnabledByEventName($key)) $count ++;
        }

        if ($count === 0) {
            $this->getEventListener()->unregisterEvent($trigger->getEventClass());
        }
    }

    public function isTriggerEnabledByEventName(string $name): bool {
        return $this->events[$name] ?? false;
    }

    public function getEnabledEvents(): array {
        return array_filter($this->events, fn(bool $v) => $v);
    }

    /**
     * @param string $event
     * @return Recipe[]
     */
    public function getAssignedRecipes(string $event): array {
        $trigger = EventTrigger::get($event);
        if ($trigger === null) return [];

        $container = TriggerHolder::global()->getRecipes($trigger);
        return $container?->getAllRecipe() ?? [];
    }
}