<?php

namespace aieuo\mineflow;

use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\ui\TriggerForm;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use aieuo\mineflow\utils\Session;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\utils\Config;

class EventListener implements Listener {

    /** @var Main */
    private $owner;

    /** @var array */
    private $enabledEvents = [];

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
        $enables = $this->enabledEvents;
        $playerEvent = "pocketmine\\event\\player\\";
        $blockEvent = "pocketmine\\event\\block\\";

        $this->registerEvent($playerEvent."PlayerJoinEvent", "onJoin");
        $this->registerEvent($playerEvent."PlayerQuitEvent", "onQuit");
        $this->registerEvent($playerEvent."PlayerInteractEvent", "onInteract");
    }

    private function registerEvent(string $event, string $method) {
        $owner = $this->getOwner();
        $pluginManager = $owner->getServer()->getPluginManager();
        $pluginManager->registerEvent($event, $this, EventPriority::NORMAL, new MethodEventExecutor($method), $this->getOwner());
    }

    public function onJoin(PlayerJoinEvent $event) {
        Session::createSession($event->getPlayer());
    }

    public function onQuit(PlayerQuitEvent $event) {
        Session::destroySession($event->getPlayer());
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
    }
}