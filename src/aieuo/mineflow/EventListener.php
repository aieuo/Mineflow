<?php

namespace aieuo\mineflow;

use pocketmine\utils\Config;
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\event\server\LowMemoryEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\EventPriority;
use pocketmine\event\Event;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\ui\TriggerForm;
use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\Main;

class EventListener implements Listener {

    /** @var Main */
    private $owner;

    /** @var array */
    private $enabledEvents = [];

    /** @var array */
    private $eventMethods = [
        "PlayerChatEvent" => "onChat",
        "PlayerCommandPreprocessEvent" => "onCommandPreprocess",
        "PlayerInteractEvent" => "onInteract",
        "PlayerJoinEvent" => "onJoin",
        "PlayerQuitEvent" => "onQuit",
        "BlockBreakEvent" => "onBlockBreak",
        "BlockPlaceEvent" => "onBlockPlace",
        "SignChangeEvent" => "onSignChange",
        "EntityDamageEvent" => "onEntityDamage",
        // "EntityAttackEvent" => "onEntityAttack",
        "PlayerToggleFlightEvent" => "onToggleFlight",
        "PlayerDeathEvent" => "onDeath",
        "EntityLevelChangeEvent" => "onLevelChange",
        "CraftItemEvent" => "onCraftItem",
        "PlayerDropItemEvent" => "onDropItem",
        "FurnaceBurnEvent" => "onFurnaceBurn",
        "LevelLoadEvent" => "onLevelLoad",
        "PlayerBedEnterEvent" => "onBedEnter",
        "PlayerChangeSkinEvent" => "onChangeSkin",
        "PlayerExhaustEvent" => "onExhaust",
        "PlayerItemConsumeEvent" => "onItemConsume",
        "PlayerMoveEvent" => "onMove",
        "PlayerToggleSneakEvent" => "onToggleSneak",
        "PlayerToggleSprintEvent" => "onToggleSprint",
        "LowMemoryEvent" => "onLowMemory",
        "ProjectileHitEntityEvent" => "onProjectileHit",
    ];

    public function __construct(Main $owner, Config $eventSettings) {
        $this->owner = $owner;
        $this->checkEventSettings($eventSettings);
    }

    private function getOwner(): Main {
        return $this->owner;
    }

    public function checkEventSettings(Config $eventSettings) {
        $defaults = TriggerManager::getManager(TriggerManager::TRIGGER_EVENT)->getDefaultEventSettings();

        $eventSettings->setDefaults($defaults);
        $eventSettings->save();

        foreach ($eventSettings->getAll() as $event => $value) {
            if ($value) $this->enabledEvents[$event] = true;
        }
    }

    public function registerEvents() {
        $manager = TriggerManager::getManager(TriggerManager::TRIGGER_EVENT);

        $this->registerEvent(PlayerJoinEvent::class, "onJoin");
        $this->registerEvent(PlayerQuitEvent::class, "onQuit");
        $this->registerEvent(PlayerInteractEvent::class, "onInteract");

        foreach ($this->enabledEvents as $event => $value) {
            $this->registerEvent($manager->getEventPath($event), $this->eventMethods[$event]);
        }
    }

    private function registerEvent(string $event, string $method) {
        $owner = $this->getOwner();
        $pluginManager = $owner->getServer()->getPluginManager();
        $pluginManager->registerEvent($event, $this, EventPriority::NORMAL, new MethodEventExecutor($method), $this->getOwner());
    }

    public function onJoin(PlayerJoinEvent $event) {
        Session::createSession($event->getPlayer());

        if (isset($this->enabledEvents["PlayerJoinEvent"])) $this->onEvent($event, "PlayerJoinEvent");
    }

    public function onQuit(PlayerQuitEvent $event) {
        Session::destroySession($event->getPlayer());

        if (isset($this->enabledEvents["PlayerQuitEvent"])) $this->onEvent($event, "PlayerQuitEvent");
    }

    public function onInteract(PlayerInteractEvent $event) {
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;

        $player = $event->getPlayer();
        $block = $event->getBlock();
        $session = Session::getSession($player);
        $manager = TriggerManager::getManager(TriggerManager::TRIGGER_BLOCK);
        $position = $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName();

        if ($player->isOp() and $session->exists("blockTriggerAction")) {
            switch ($session->get("blockTriggerAction")) {
                case "add":
                    $recipe = $session->get("blockTriggerRecipe");
                    $trigger = [TriggerManager::TRIGGER_BLOCK, $position];
                    if ($recipe->existsTrigger($trigger)) {
                        (new TriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.block.alreadyExists"]);
                        return;
                    }
                    $recipe->addTrigger($trigger);
                    (new TriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.block.add.success"]);
                    break;
            }
            $session->remove("blockTriggerAction");
            return;
        }
        if ($manager->exists($position)) {
            $recipes = $manager->get($position);
            $recipes->executeAll($player);
        }

        if (isset($this->enabledEvents["PlayerInteractEvent"])) $this->onEvent($event, "PlayerInteractEvent");
    }

    public function onEvent(Event $event, string $eventName): void {

    }

    public function onChat(PlayerChatEvent $event) {
        $this->onEvent($event, "PlayerChatEvent");
    }
    public function onCommandPreprocess(PlayerCommandPreprocessEvent $event) {
        $this->onEvent($event, "PlayerCommandPreprocessEvent");
    }
    public function onBlockBreak(BlockBreakEvent $event) {
        $this->onEvent($event, "BlockBreakEvent");
    }
    public function onBlockPlace(BlockPlaceEvent $event) {
        $this->onEvent($event, "BlockPlaceEvent");
    }
    public function onSignChange(SignChangeEvent $event) {
        $this->onEvent($event, "SignChangeEvent");
    }
    public function onEntityDamage(EntityDamageEvent $event) {
        $this->onEvent($event, "EntityDamageEvent");
    }
    public function onToggleFlight(PlayerToggleFlightEvent $event) {
        $this->onEvent($event, "PlayerToggleFlightEvent");
    }
    public function onDeath(PlayerDeathEvent $event) {
        $this->onEvent($event, "PlayerDeathEvent");
    }
    public function onLevelChange(EntityLevelChangeEvent $event) {
        $this->onEvent($event, "EntityLevelChangeEvent");
    }
    public function onCraftItem(CraftItemEvent $event) {
        $this->onEvent($event, "CraftItemEvent");
    }
    public function onDropItem(PlayerDropItemEvent $event) {
        $this->onEvent($event, "PlayerDropItemEvent");
    }
    public function onFurnaceBurn(FurnaceBurnEvent $event) {
        $this->onEvent($event, "FurnaceBurnEvent");
    }
    public function onLevelLoad(LevelLoadEvent $event) {
        $this->onEvent($event, "LevelLoadEvent");
    }
    public function onBedEnter(PlayerBedEnterEvent $event) {
        $this->onEvent($event, "PlayerBedEnterEvent");
    }
    public function onChangeSkin(PlayerChangeSkinEvent $event) {
        $this->onEvent($event, "PlayerChangeSkinEvent");
    }
    public function onExhaust(PlayerExhaustEvent $event) {
        $this->onEvent($event, "PlayerExhaustEvent");
    }
    public function onItemConsume(PlayerItemConsumeEvent $event) {
        $this->onEvent($event, "PlayerItemConsumeEvent");
    }
    public function onMove(PlayerMoveEvent $event) {
        $this->onEvent($event, "PlayerMoveEvent");
    }
    public function onToggleSneak(PlayerToggleSneakEvent $event) {
        $this->onEvent($event, "PlayerToggleSneakEvent");
    }
    public function onToggleSprint(PlayerToggleSprintEvent $event) {
        $this->onEvent($event, "PlayerToggleSprintEvent");
    }
    public function onLowMemory(LowMemoryEvent $event) {
        $this->onEvent($event, "LowMemoryEvent");
    }
    public function onProjectileHit(ProjectileHitEntityEvent $event) {
        $this->onEvent($event, "ProjectileHitEntityEvent");
    }
}