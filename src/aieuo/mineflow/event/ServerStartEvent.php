<?php
declare(strict_types=1);

namespace aieuo\mineflow\event;

use aieuo\mineflow\Main;
use pocketmine\event\plugin\PluginEvent;

class ServerStartEvent extends PluginEvent {

    /** @var float */
    private $microtime;

    /** @var string */
    private $date;

    public function __construct(Main $owner) {
        parent::__construct($owner);
        $this->microtime = microtime(true);
        $this->date = date("Y-m-d H:i:s");
    }

    public function getMicrotime(): float {
        return $this->microtime;
    }

    public function getDate(): string {
        return $this->date;
    }
}