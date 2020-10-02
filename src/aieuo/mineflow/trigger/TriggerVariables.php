<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\event\EntityAttackEvent;
use aieuo\mineflow\variable\DummyVariable;
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

class TriggerVariables {

    public static function get(Trigger $trigger): array {
        $variables = [];
        switch ($trigger->getType()) {
            case Trigger::TYPE_BLOCK:
                $variables = [new DummyVariable("block", DummyVariable::BLOCK)];
                break;
            case Trigger::TYPE_COMMAND:
                $variables = [
                    new DummyVariable("cmd", DummyVariable::STRING),
                    new DummyVariable("args", DummyVariable::LIST),
                ];
                break;
            case Trigger::TYPE_EVENT:
                switch ($trigger->getKey()) {
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case PlayerMoveEvent::class:
                        $variables["move_from"] = new DummyVariable("move_from", DummyVariable::LOCATION);
                        $variables["move_to"] = new DummyVariable("move_to", DummyVariable::LOCATION);
                    case PlayerDeathEvent::class:
                    case PlayerChangeSkinEvent::class:
                    case PlayerJoinEvent::class:
                    case PlayerQuitEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::PLAYER);
                        break;
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case SignChangeEvent::class:
                        $variables["sign_lines"] = new DummyVariable("sign_lines", DummyVariable::LIST);
                    case PlayerInteractEvent::class:
                    case BlockBreakEvent::class:
                    case BlockPlaceEvent::class:
                    case PlayerBedEnterEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::PLAYER);
                        $variables["block"] = new DummyVariable("block", DummyVariable::BLOCK);
                        break;
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case PlayerChatEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::PLAYER);
                        $variables["message"] = new DummyVariable("message", DummyVariable::STRING);
                    case PlayerCommandPreprocessEvent::class:
                        $variables["cmd"] = new DummyVariable("cmd", DummyVariable::STRING);
                        $variables["args"] = new DummyVariable("args", DummyVariable::LIST);
                        break;
                    case PlayerToggleSneakEvent::class:
                    case PlayerToggleFlightEvent::class:
                    case PlayerToggleSprintEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::PLAYER);
                        $variables["state"] = new DummyVariable("state", DummyVariable::STRING);
                        break;
                    case EntityLevelChangeEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::ENTITY);
                        $variables["origin_level"] = new DummyVariable("origin_level", DummyVariable::LEVEL);
                        $variables["target_level"] = new DummyVariable("target_level", DummyVariable::LEVEL);
                        break;
                    case PlayerDropItemEvent::class:
                    case PlayerItemConsumeEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::PLAYER);
                        $variables["item"] = new DummyVariable("item", DummyVariable::ITEM);
                        break;
                    case CraftItemEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::PLAYER);
                        $variables["inputs"] = new DummyVariable("inputs", DummyVariable::LIST);
                        $variables["outputs"] = new DummyVariable("outputs", DummyVariable::LIST);
                        break;
                    case EntityDamageEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::PLAYER);
                        $variables["damage"] = new DummyVariable("damage", DummyVariable::NUMBER);
                        $variables["cause"] = new DummyVariable("cause", DummyVariable::NUMBER);
                        $variables["target"] = new DummyVariable("damager", DummyVariable::PLAYER);
                        break;
                    case EntityAttackEvent::class:
                        $variables["damaged"] = new DummyVariable("target", DummyVariable::PLAYER);
                        $variables["damage"] = new DummyVariable("damage", DummyVariable::NUMBER);
                        $variables["cause"] = new DummyVariable("cause", DummyVariable::NUMBER);
                        $variables["target"] = new DummyVariable("damager", DummyVariable::PLAYER);
                        break;
                    case LevelLoadEvent::class:
                        $variables["level"] = new DummyVariable("level", DummyVariable::LEVEL);
                        break;
                    case PlayerExhaustEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::PLAYER);
                        $variables["amount"] = new DummyVariable("amount", DummyVariable::NUMBER);
                        $variables["cause"] = new DummyVariable("cause", DummyVariable::NUMBER);
                        break;
                    case ProjectileHitEntityEvent::class:
                        $variables["target"] = new DummyVariable("target", DummyVariable::ENTITY);
                        break;
                    case FurnaceBurnEvent::class:
                        $variables["fuel"] = new DummyVariable("fuel". DummyVariable::ITEM);
                        break;
                }
                break;
        }
        return $variables;
    }

}