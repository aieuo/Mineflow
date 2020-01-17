<?php

namespace aieuo\mineflow\variable;

use pocketmine\tile\Sign;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\entity\Entity;
use pocketmine\block\SignPost;
use pocketmine\block\Block;
use pocketmine\Server;
use pocketmine\Player;

class DefaultVariables {

    public static function getServerVariables(): array {
        $server = Server::getInstance();
        $onlines = array_map(function (Player $player) {
            return $player->getName();
        }, array_values($server->getOnlinePlayers()));
        return [
            "server_name" => new StringVariable($server->getName(), "server_name"),
            "microtime" => new NumberVariable(microtime(true), "microtime"),
            "time" => new StringVariable(date("h:i:s"), "time"),
            "date" => new StringVariable(date("m/d"), "date"),
            "default_level" => new StringVariable($server->getDefaultLevel()->getFolderName(), "default_level"),
            "onlines" => new ListVariable($onlines, "onlines"),
            "ops" => new ListVariable($server->getOps()->getAll(true), "ops"),
        ];
    }

    public static function getEntityVariables(Entity $target, string $name = "target"): array {
        return [
            $name => new MapVariable([
                "id" => $target->getId(),
                "nametag" => $target->getNameTag(),
                "x" => $target->x,
                "y" => $target->y,
                "z" => $target->z,
                "level" => $target->level->getFolderName(),
            ], $name, $target->__toString()),
        ];
    }

    public static function getPlayerVariables(Player $target, string $name = "target"): array {
        $hand = $target->getInventory()->getItemInHand();
        return [
            $name => new MapVariable([
                "id" => $target->getId(),
                "name" => $target->getName(),
                "nametag" => $target->getNameTag(),
                "x" => $target->x,
                "y" => $target->y,
                "z" => $target->z,
                "level" => $target->level->getFolderName(),
                "hand" => new MapVariable([
                    "index" => $target->getInventory()->getHeldItemIndex(),
                    "name" => $hand->getName(),
                    "id" => $hand->getId(),
                    "damage" => $hand->getDamage(),
                    "count" => $hand->getCount(),
                ], "hand", $hand->__toString())
            ], $name, $target->__toString()),
        ];
    }

    public static function getBlockVariables(Block $block, string $name = "block", bool $checkSign = true): array {
        $variables = [
            $name => new MapVariable([
                "name" => $block->getName(),
                "id" => $block->getId(),
                "damage" => $block->getDamage(),
                "x" => $block->x,
                "y" => $block->y,
                "z" => $block->z,
                "level" => $block->level->getFolderName(),
            ], $name, $block->__toString()),
        ];
        if ($checkSign and $block instanceof SignPost) {
            $sign = $block->level->getTile($block);
            if ($sign instanceof Sign) {
                $variables["sign_lines"] = new ListVariable("sign_lines", $sign->getText());
            }
        }
        return $variables;
    }

    public static function getCommandVariables(string $command): array {
        $commands = explode(" ", $command);
        return [
            "cmd" => new StringVariable(array_shift($commands), "cmd"),
            "args" => new ListVariable($commands, "args"),
        ];
    }

    public static function getEventVariables(Event $event, string $eventName): array {
        $variables = [];
        switch ($eventName) {
            case "PlayerJoinEvent":
            case "PlayerQuitEvent":
                $target = $event->getPlayer();
                $variables = array_merge($variables, self::getPlayerVariables($target));
                break;
            case "SignChangeEvent":
                $lines = $event->getLines();
                $variables["sign_lines"] = new ListVariable($lines, "sign_lines");
                $target = $event->getPlayer();
                $block = $event->getBlock();
                $variables = array_merge($variables, self::getPlayerVariables($target), self::getBlockVariables($block, "block", isset($lines)));
                break;
            case "PlayerInteractEvent":
            case "BlockBreakEvent":
            case "BlockPlaceEvent":
                $target = $event->getPlayer();
                $block = $event->getBlock();
                $variables = array_merge($variables, self::getPlayerVariables($target), self::getBlockVariables($block, "block", isset($lines)));
                break;
            case "PlayerBedEnterEvent":
                $target = $event->getPlayer();
                $block = $event->getBed();
                $variables = array_merge(self::getPlayerVariables($target), self::getBlockVariables($block));
                break;
            case "PlayerCommandPreprocessEvent":
                $variables = array_merge($variables, self::getCommandVariables(substr($event->getMessage(), 1)));
                $target = $event->getPlayer();
                $variables = array_merge($variables, self::getPlayerVariables($target));
                $variables["message"] = new StringVariable($event->getMessage(), "message");
                break;
            case "PlayerChatEvent":
                $target = $event->getPlayer();
                $variables = array_merge($variables, self::getPlayerVariables($target));
                $variables["message"] = new StringVariable($event->getMessage(), "message");
                break;
            case "PlayerToggleSneakEvent":
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable($event->isSneaking(), "state");
                break;
            case "PlayerToggleSprintEvent":
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable($event->isSprinting(), "state");
                break;
            case "PlayerToggleFlightEvent":
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable($event->isFlying(), "state");
                break;
            case "EntityLevelChangeEvent":
                $target = $event->getEntity();
                $variables = self::getEntityVariables($target);
                $variables["origin_level"] = new StringVariable($event->getOrigin()->getFolderName(), "origin_level");
                $variables["target_level"] = new StringVariable($event->getTarget()->getFolderName(), "target_level");
                break;
            case "PlayerDropItemEvent":
            case "PlayerItemConsumeEvent":
                $target = $event->getPlayer();
                $item = $event->getItem();
                $variables = array_merge(self::getPlayerVariables($target), [
                    "item" => new MapVariable([
                        "name" => $item->getName(),
                        "id" => $item->getId(),
                        "damage" => $item->getDamage(),
                        "count" => $item->getCount(),
                    ], "item", $item->__toString()),
                ]);
                break;
            case "CraftItemEvent":
                $target = $event->getPlayer();
                $inputs = array_map(function (Item $input) {
                    return new MapVariable([
                        "name" => $input->getName(),
                        "id" => $input->getId(),
                        "damage" => $input->getDamage(),
                        "count" => $input->getCount(),
                    ], "input", $input->__toString());
                }, $event->getInputs());
                $outputs = array_map(function (Item $output) {
                    return new MapVariable([
                        "name" => $output->getName(),
                        "id" => $output->getId(),
                        "damage" => $output->getDamage(),
                        "count" => $output->getCount(),
                    ], "output", $output->__toString());
                }, $event->getInputs());
                $variables = self::getPlayerVariables($target);
                $variables["inputs"] = new ListVariable($inputs, "inputs");
                $variables["outputs"] = new ListVariable($outputs, "outputs");
                break;
            case "EntityDamageEvent":
                $target = $event->getEntity();
                $variables = $target instanceof Player ? self::getPlayerVariables($target) : self::getEntityVariables($target);
                $variables["damage"] = new NumberVariable($event->getBaseDamage(), "damage");
                $variables["cause"] = new NumberVariable($event->getCause(), "cause");
                if ($event instanceof EntityDamageByEntityEvent) {
                    $damager = $event->getDamager();
                    if ($damager instanceof Player) $add = self::getPlayerVariables($damager, "damager");
                    elseif ($damager instanceof Entity) $add = self::getEntityVariables($damager, "damager");
                    else $add = [];
                    $variables = array_merge($variables, $add);
                }
                break;
            case "EntityAttackEvent":
                $target = $event->getDamager();
                if ($target === null) break;
                $variables = $target instanceof Player ? self::getPlayerVariables($target) : self::getEntityVariables($target);
                $variables["damage"] = new NumberVariable($event->getBaseDamage(), "damage");
                $variables["cause"] = new NumberVariable($event->getCause(), "cause");
                $damaged = $event->getEntity();
                $add = $damaged instanceof Player ? self::getPlayerVariables($damaged, "damaged") : self::getEntityVariables($damaged, "damaged");
                $variables = array_merge($variables, $add);
                break;
            case "PlayerMoveEvent":
            case "PlayerDeathEvent":
            case "PlayerChangeSkinEvent":
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                break;
            case "LevelLoadEvent":
                $level = $event->getLevel();
                $variables = [
                    "id" => $level->getId(),
                    "name" => $level->getFolderName(),
                ];
                break;
            case "PlayerExhaustEvent":
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["amount"] = $event->getAmount();
                $variables["cause"] = $event->getCause();
                break;
            case "ProjectileHitEntityEvent":
                $target = $event->getEntity();
                $variables = $target instanceof Player ? self::getPlayerVariables($target) : self::getEntityVariables($target);
                $entityHit = $event->getEntityHit();
                $add = $entityHit instanceof Player ? self::getPlayerVariables($entityHit) : self::getEntityVariables($entityHit);
                $variables = array_merge($variables, $add);
                break;
            case "FurnaceBurnEvent":
                $fuel = $event->getFuel();
                $variables = [
                    "fuel" => new MapVariable([
                        "name" => $fuel->getName(),
                        "id" => $fuel->getId(),
                        "damage" => $fuel->getDamage(),
                        "count" => $fuel->getCount(),
                    ], "fuel", $fuel->__toString()),
                ];
                break;
        }
        return $variables;
    }
}