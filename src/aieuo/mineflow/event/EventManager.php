<?php

namespace aieuo\mineflow\event;

use aieuo\mineflow\trigger\EventTriggers;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\utils\Config;

class EventManager {

    /** @var Config */
    private $config;

    /** @var array */
    private $eventPaths = [
        "PlayerChatEvent" => PlayerChatEvent::class,
        "PlayerCommandPreprocessEvent" => PlayerCommandPreprocessEvent::class,
        "PlayerInteractEvent" => PlayerInteractEvent::class,
        "PlayerJoinEvent" => PlayerJoinEvent::class,
        "PlayerQuitEvent" => PlayerQuitEvent::class,
        "BlockBreakEvent" => BlockBreakEvent::class,
        "BlockPlaceEvent" => BlockPlaceEvent::class,
        "ServerStartEvent" => ServerStartEvent::class,
        "SignChangeEvent" => SignChangeEvent::class,
        "EntityDamageEvent" => EntityDamageEvent::class,
        "EntityAttackEvent" => EntityDamageByEntityEvent::class,
        "PlayerToggleFlightEvent" => PlayerToggleFlightEvent::class,
        "PlayerDeathEvent" => PlayerDeathEvent::class,
        "EntityLevelChangeEvent" => EntityLevelChangeEvent::class,
        "CraftItemEvent" => CraftItemEvent::class,
        "PlayerDropItemEvent" => PlayerDropItemEvent::class,
        "FurnaceBurnEvent" => FurnaceBurnEvent::class,
        "LevelLoadEvent" => LevelLoadEvent::class,
        "PlayerBedEnterEvent" => PlayerBedEnterEvent::class,
        "PlayerChangeSkinEvent" => PlayerChangeSkinEvent::class,
        "PlayerExhaustEvent" => PlayerExhaustEvent::class,
        "PlayerItemConsumeEvent" => PlayerItemConsumeEvent::class,
        "PlayerMoveEvent" => PlayerMoveEvent::class,
        "PlayerToggleSneakEvent" => PlayerToggleSneakEvent::class,
        "PlayerToggleSprintEvent" => PlayerToggleSprintEvent::class,
        "ProjectileHitEntityEvent" => ProjectileHitEntityEvent::class,
    ];

    /** @var array */
    private $defaultEnableEvents = [
        "PlayerChatEvent",
        "PlayerCommandPreprocessEvent",
        "PlayerInteractEvent",
        "PlayerJoinEvent",
        "BlockBreakEvent",
        "BlockPlaceEvent",
        "ServerStartEvent",
        "EntityDamageEvent",
        "EntityAttackEvent",
        "PlayerToggleFlightEvent",
        "PlayerDeathEvent",
        "EntityLevelChangeEvent",
        "CraftItemEvent",
    ];

    /** @var array */
    private $enabledEvents = [];

    public function __construct(Config $events) {
        $this->config = $events;

        $this->checkEventSettings();
    }

    public function getEventConfig(): Config {
        return $this->config;
    }

    public function getEvents(): array {
        return $this->eventPaths;
    }

    public function getEventPath(string $event): ?string {
        return $this->eventPaths[$event] ?? null;
    }

    public function getDefaultEventSettings(): array {
        $events = $this->getEvents();
        $enables = $this->defaultEnableEvents;
        $settings = [];

        foreach ($events as $event => $value) {
            $settings[$event] = in_array($event, $enables);
        }
        return $settings;
    }

    private function checkEventSettings() {
        $defaults = $this->getDefaultEventSettings();

        $this->config->setDefaults($defaults);
        $this->config->save();

        foreach ($this->config->getAll() as $event => $value) {
            if ($value) $this->enabledEvents[$event] = true;
        }
    }

    public function getEnabledEvents(): array {
        return $this->enabledEvents;
    }

    public function setEventEnabled(string $event, bool $enable) {
        $this->config->set($event, $enable);
        $this->config->save();

        if ($enable) {
            $this->enabledEvents[$event] = true;
        } else {
            unset($this->enabledEvents[$event]);
        }
    }

    public function isEnabledEvent(string $event) {
        return isset($this->enabledEvents[$event]);
    }

    public function getAssignedRecipes(string $event): array {
        $recipes = [];
        $containers = TriggerHolder::getInstance()->getRecipesWithSubKey(new Trigger(Trigger::TYPE_EVENT, $event));
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