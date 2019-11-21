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
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\ListVariable;

class DefaultVariables {

    public static function getServerVariables(): array {
        $server = Server::getInstance();
        $onlines = array_map(function (Player $player) {
            return $player->getName();
        }, array_values($server->getOnlinePlayers()));
        $variables = [
            "server_name" => new StringVariable("server_name", $server->getName()),
            "microtime" => new NumberVariable("microtime", microtime(true)),
            "time" => new StringVariable("time", date("h:i:s")),
            "date" => new StringVariable("date", date("m/d")),
            "default_level" => new StringVariable("default_level", $server->getDefaultLevel()->getFolderName()),
            "onlines" => new ListVariable("onlines", $onlines),
            "ops" => new ListVariable("ops", $server->getOps()->getAll(true)),
        ];
        return $variables;
    }

    public static function getEntityVariables(Entity $target, string $name = "target"): array {
        $variables = [
            $name => new MapVariable($name, [
                "id" => $target->getId(),
                "nametag" => $target->getNameTag(),
                "x" => $target->x,
                "y" => $target->y,
                "z" => $target->z,
                "level" => $target->level->getFolderName(),
            ], $target->__toString()),
        ];
        return $variables;
    }

    public static function getPlayerVariables(Player $target, string $name = "target"): array {
        $hand = $target->getInventory()->getItemInHand();
        $variables = [
            $name => new MapVariable($name, [
                "id" => $target->getId(),
                "name" => $target->getName(),
                "nametag" => $target->getNameTag(),
                "x" => $target->x,
                "y" => $target->y,
                "z" => $target->z,
                "level" => $target->level->getFolderName(),
                "hand" => new MapVariable("hand", [
                    "index" => $target->getInventory()->getHeldItemIndex(),
                    "name" => $hand->getName(),
                    "id" => $hand->getId(),
                    "damage" => $hand->getDamage(),
                    "count" => $hand->getCount(),
                ], $hand->__toString())
            ], $target->__toString()),
        ];
        return $variables;
    }

    public static function getBlockVariables(Block $block, string $name = "block", bool $checkSign = true): array {
        $variables = [
            $name => new MapVariable($name, [
                "name" => $block->getName(),
                "id" => $block->getId(),
                "damage" => $block->getDamage(),
                "x" => $block->x,
                "y" => $block->y,
                "z" => $block->z,
                "level" => $block->level->getFolderName(),
            ], $block->__toString()),
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
        $variables = [
            "cmd" => new StringVariable("cmd", array_shift($commands)),
            "args" => new ListVariable("args", $commands),
        ];
        return $variables;
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
                $variables["sign_lines"] = new ListVariable("sign_lines", $lines);
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
            case "PlayerChatEvent":
                $target = $event->getPlayer();
                $variables = array_merge($variables, self::getPlayerVariables($target));
                $variables["message"] = new StringVariable("message", $event->getMessage());
                break;
            case "PlayerToggleSneakEvent":
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable("state", $event->isSneaking());
                break;
            case "PlayerToggleSprintEvent":
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable("state", $event->isSprinting());
                break;
            case "PlayerToggleFlightEvent":
                $target = $event->getPlayer();
                $variables = self::getPlayerVariables($target);
                $variables["state"] = new StringVariable("state", $event->isFlying());
                break;
            case "EntityLevelChangeEvent":
                $target = $event->getEntity();
                $variables = self::getEntityVariables($target);
                $variables["origin_level"] = new StringVariable("origin_level", $event->getOrigin()->getFolderName());
                $variables["target_level"] = new StringVariable("target_level", $event->getTarget()->getFolderName());
                break;
            case "PlayerDropItemEvent":
            case "PlayerItemConsumeEvent":
                $target = $event->getPlayer();
                $item = $event->getItem();
                $variables = array_merge(self::getPlayerVariables($target), [
                    "item" => new MapVariable("item", [
                        "name" => $item->getName(),
                        "id" => $item->getId(),
                        "damage" => $item->getDamage(),
                        "count" => $item->getCount(),
                    ], $item->__toString()),
                ]);
                break;
            case "CraftItemEvent":
                $target = $event->getPlayer();
                $inputs = array_map(function (Item $input) {
                    return new MapVariable("input", [
                        "name" => $input->getName(),
                        "id" => $input->getId(),
                        "damage" => $input->getDamage(),
                        "count" => $input->getCount(),
                    ], $input->__toString());
                }, $event->getInputs());
                $outputs = array_map(function (Item $output) {
                    return new MapVariable("output", [
                        "name" => $output->getName(),
                        "id" => $output->getId(),
                        "damage" => $output->getDamage(),
                        "count" => $output->getCount(),
                    ], $output->__toString());
                }, $event->getInputs());
                $variables = self::getPlayerVariables($target);
                $variables["inputs"] = new ListVariable("inputs", $inputs);
                $variables["outputs"] = new ListVariable("outputs", $outputs);
                break;
            case "EntityDamageEvent":
                $target = $event->getEntity();
                $variables = $target instanceof Player ? self::getPlayerVariables($target) : self::getEntityVariables($target);
                $variables["damage"] = new NumberVariable("damage", $event->getBaseDamage());
                $variables["cause"] = new NumberVariable("cause", $event->getCause());
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
                $variables["damage"] = new NumberVariable("damage", $event->getBaseDamage());
                $variables["cause"] = new NumberVariable("cause", $event->getCause());
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
                    "fuel" => new MapVariable("fuel", [
                        "name" => $fuel->getName(),
                        "id" => $fuel->getId(),
                        "damage" => $fuel->getDamage(),
                        "count" => $fuel->getCount(),
                    ], $fuel->__toString()),
                ];
                break;
        }
        return $variables;
    }
}