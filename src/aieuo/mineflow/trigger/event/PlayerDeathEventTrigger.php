<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;

class PlayerDeathEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerDeathEvent", PlayerDeathEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerDeathEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getPlayerVariables($target);
        $cause = $target->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $killer = $cause->getDamager();
            if ($killer instanceof Player) {
                $variables = array_merge($variables, DefaultVariables::getPlayerVariables($killer, "killer"));
            }
        }
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "killer" => new DummyVariable(PlayerVariable::class),
        ];
    }
}