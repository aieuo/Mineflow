<?php
declare(strict_types=1);

namespace aieuo\mineflow\event;

use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\utils\Language;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
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
use pocketmine\event\player\PlayerJumpEvent;
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
    private $fullNames = [
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
        "EntityAttackEvent" => EntityAttackEvent::class,
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
        "PlayerJumpEvent" => PlayerJumpEvent::class,
    ];

    /** @var array */
    private $defaultEnableEvents = [
        PlayerChatEvent::class,
        PlayerCommandPreprocessEvent::class,
        PlayerInteractEvent::class,
        PlayerJoinEvent::class,
        BlockBreakEvent::class,
        BlockPlaceEvent::class,
        ServerStartEvent::class,
        EntityDamageEvent::class,
        EntityAttackEvent::class,
        PlayerToggleFlightEvent::class,
        PlayerDeathEvent::class,
        EntityLevelChangeEvent::class,
        CraftItemEvent::class,
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
        return $this->config->getAll(true);
    }

    public function getFullName(string $event): ?string {
        return $this->fullNames[$event] ?? null;
    }

    public function getEventName(string $fullName) {
        $names = explode("\\", $fullName);
        return end($names);
    }

    public function getDefaultEventSettings(): array {
        $enables = $this->defaultEnableEvents;
        $settings = [];

        foreach ($this->fullNames as $event => $value) {
            $settings[$value] = in_array($value, $enables, true);
        }
        return $settings;
    }

    private function checkEventSettings(): void {
        $defaults = $this->getDefaultEventSettings();

        $isOld = false;
        $new = [];
        foreach ($this->config->getAll() as $event => $value) {
            $full = $this->getFullName($event) ?? $event;
            $new[$full] = $value;

            if ($full !== $event) $isOld = true;
        }
        if ($isOld) $this->config->setAll($new);

        $this->config->setDefaults($defaults);
        $this->config->save();

        foreach ($this->config->getAll() as $event => $value) {
            if ($value) $this->enabledEvents[$event] = true;
        }
    }

    public function getEnabledEvents(): array {
        return $this->enabledEvents;
    }

    public function setEventEnabled(string $event, bool $enable): void {
        $this->config->set($event, $enable);
        $this->config->save();

        if ($enable) {
            $this->enabledEvents[$event] = true;
        } else {
            unset($this->enabledEvents[$event]);
        }
    }

    public function isEnabledEvent(string $event): bool {
        return isset($this->enabledEvents[$event]);
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

    public function translateEventName(string $event): string {
        return Language::exists("trigger.event.".$event) ? Language::get("trigger.event.".$event) : $event;
    }
}