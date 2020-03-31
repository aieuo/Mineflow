<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\BlockObjectVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\variable\object\LevelObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
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
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\tile\Sign;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\entity\Entity;
use pocketmine\block\Block;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\tile\Tile;

class DefaultVariables {

    public static function getServerVariables(): array {
        $server = Server::getInstance();
        $onlines = array_map(function (Player $player) {
            return new StringVariable($player->getName());
        }, array_values($server->getOnlinePlayers()));
        return [
            "server_name" => new StringVariable($server->getName(), "server_name"),
            "microtime" => new NumberVariable(microtime(true), "microtime"),
            "time" => new StringVariable(date("h:i:s"), "time"),
            "date" => new StringVariable(date("m/d"), "date"),
            "default_level" => new StringVariable($server->getDefaultLevel()->getFolderName(), "default_level"),
            "onlines" => new ListVariable($onlines, "onlines"),
            "ops" => new ListVariable(array_map(function (string $name) { return new StringVariable($name); }, $server->getOps()->getAll(true)), "ops"),
        ];
    }

    public static function getEntityVariables(Entity $target, string $name = "target"): array {
        if ($target instanceof Player) return self::getPlayerVariables($target, $name);
        return [$name => new EntityObjectVariable($target, $name, $target->getNameTag())];
    }

    public static function getPlayerVariables(Player $target, string $name = "target"): array {
        return [$name => new PlayerObjectVariable($target, $name, $target->getName())];
    }

    public static function getBlockVariables(Block $block, string $name = "block"): array {
        return [$name => new BlockObjectVariable($block, $name, $block->getId().":".$block->getDamage())];
    }

    public static function getCommandVariables(string $command): array {
        $commands = explode(" ", $command);
        return [
            "cmd" => new StringVariable(array_shift($commands), "cmd"),
            "args" => new ListVariable(array_map(function (string $cmd) { return new StringVariable($cmd); }, $commands), "args"),
        ];
    }

    public static function getEventVariables(Event $event, string $eventName): array {
        $variables = [];
        switch ($event) {
            case $event instanceof PlayerMoveEvent:
            case $event instanceof PlayerDeathEvent:
            case $event instanceof PlayerChangeSkinEvent:
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                break;
            case $event instanceof PlayerJoinEvent:
            case $event instanceof PlayerQuitEvent:
                $target = $event->getPlayer();
                $variables = array_merge($variables, self::getPlayerVariables($target));
                break;
            case $event instanceof SignChangeEvent:
                $lines = $event->getLines();
                $variables["sign_lines"] = new ListVariable(array_map(function (string $line) { return new StringVariable($line); }, $lines), "sign_lines");
                $target = $event->getPlayer();
                $block = $event->getBlock();
                $variables = array_merge($variables, self::getPlayerVariables($target), self::getBlockVariables($block));
                break;
            case $event instanceof PlayerInteractEvent:
            case $event instanceof BlockBreakEvent:
            case $event instanceof BlockPlaceEvent:
                $target = $event->getPlayer();
                $block = $event->getBlock();
                $variables = array_merge($variables, self::getPlayerVariables($target), self::getBlockVariables($block));
                break;
            case $event instanceof PlayerBedEnterEvent:
                $target = $event->getPlayer();
                $block = $event->getBed();
                $variables = array_merge(self::getPlayerVariables($target), self::getBlockVariables($block));
                break;
            case $event instanceof PlayerCommandPreprocessEvent:
                $variables = array_merge($variables, self::getCommandVariables(substr($event->getMessage(), 1)));
                $target = $event->getPlayer();
                $variables = array_merge($variables, self::getPlayerVariables($target));
                $variables["message"] = new StringVariable($event->getMessage(), "message");
                break;
            case $event instanceof PlayerChatEvent:
                $target = $event->getPlayer();
                $variables = array_merge($variables, self::getPlayerVariables($target));
                $variables["message"] = new StringVariable($event->getMessage(), "message");
                break;
            case $event instanceof PlayerToggleSneakEvent:
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable($event->isSneaking(), "state");
                break;
            case $event instanceof PlayerToggleSprintEvent:
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable($event->isSprinting(), "state");
                break;
            case $event instanceof PlayerToggleFlightEvent:
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable($event->isFlying(), "state");
                break;
            case $event instanceof EntityLevelChangeEvent:
                $target = $event->getEntity();
                $variables = self::getEntityVariables($target);
                $variables["origin_level"] = new LevelObjectVariable($event->getOrigin(), "origin_level");
                $variables["target_level"] = new LevelObjectVariable($event->getTarget(), "target_level");
                break;
            case $event instanceof PlayerDropItemEvent:
            case $event instanceof PlayerItemConsumeEvent:
                $target = $event->getPlayer();
                $item = $event->getItem();
                $variables = array_merge(self::getPlayerVariables($target), [
                    "item" => new ItemObjectVariable($item, "item", $item->__toString()),
                ]);
                break;
            case $event instanceof CraftItemEvent:
                $target = $event->getPlayer();
                $inputs = array_map(function (Item $input) {
                    return new ItemObjectVariable($input, "input", $input->__toString());
                }, $event->getInputs());
                $outputs = array_map(function (Item $output) {
                    return new ItemObjectVariable($output, "output", $output->__toString());
                }, $event->getInputs());
                $variables = self::getPlayerVariables($target);
                $variables["inputs"] = new ListVariable($inputs, "inputs");
                $variables["outputs"] = new ListVariable($outputs, "outputs");
                break;
            case $event instanceof EntityDamageEvent:
                $target = $event->getEntity();
                $name = $eventName === "EntityAttackEvent" ? "damaged" : "target";
                $variables = $target instanceof Player ? self::getPlayerVariables($target, $name) : self::getEntityVariables($target, $name);
                $variables["damage"] = new NumberVariable($event->getBaseDamage(), "damage");
                $variables["cause"] = new NumberVariable($event->getCause(), "cause");
                if ($event instanceof EntityDamageByEntityEvent) {
                    $damager = $event->getDamager();
                    $add = [];
                    if ($eventName === "EntityDamageEvent") {
                        if ($damager instanceof Player) $add = self::getPlayerVariables($damager, "damager");
                        elseif ($damager instanceof Entity) $add = self::getEntityVariables($damager, "damager");
                    } elseif ($eventName === "EntityAttackEvent") {
                        if ($damager instanceof Player) $add = self::getPlayerVariables($damager, "target");
                        elseif ($damager instanceof Entity) $add = self::getEntityVariables($damager, "target");
                    }
                    $variables = array_merge($variables, $add);
                }
                break;
            case $event instanceof LevelLoadEvent:
                $variables = ["level" => new LevelObjectVariable($event->getLevel())];
                break;
            case $event instanceof PlayerExhaustEvent:
                $target = $event->getPlayer();
                $variables = self::getEntityVariables($target);
                $variables["amount"] = $event->getAmount();
                $variables["cause"] = $event->getCause();
                break;
            case $event instanceof ProjectileHitEntityEvent: // TODO: player?
                $target = $event->getEntity();
                $variables = self::getEntityVariables($target);
                $entityHit = $event->getEntityHit();
                $add = $entityHit instanceof Player ? self::getPlayerVariables($entityHit) : self::getEntityVariables($entityHit);
                $variables = array_merge($variables, $add);
                break;
            case $event instanceof FurnaceBurnEvent:
                $fuel = $event->getFuel();
                $variables = [
                    "fuel" => new ItemObjectVariable($fuel, "fuel"),
                ];
                break;
        }
        return $variables;
    }
}