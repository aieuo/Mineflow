<?php
declare(strict_types=1);

namespace aieuo\mineflow\event;

use aieuo\mineflow\Main;
use pocketmine\event\Cancellable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\plugin\PluginEvent;

class EntityAttackEvent extends PluginEvent implements Cancellable {
    /** @var EntityDamageByEntityEvent */
    private $event;

    public function __construct(Main $plugin, EntityDamageByEntityEvent $event) {
        parent::__construct($plugin);
        $this->event = $event;
    }

    public function getDamageEvent(): EntityDamageByEntityEvent {
        return $this->event;
    }
}