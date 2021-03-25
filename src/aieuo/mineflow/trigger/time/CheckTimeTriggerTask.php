<?php

namespace aieuo\mineflow\trigger\time;

use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\trigger\Triggers;
use pocketmine\scheduler\Task;

class CheckTimeTriggerTask extends Task {

    /* @var TriggerHolder */
    private $triggerHolder;

    public function __construct() {
        $this->triggerHolder = TriggerHolder::getInstance();
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    public function onRun(int $currentTick) {
        $trigger = TimeTrigger::create(date("H"), date("i"));
        if ($this->triggerHolder->existsRecipe($trigger)) {
            $recipes = $this->triggerHolder->getRecipes($trigger);
            $variables = $trigger->getVariables((int)microtime(true));
            $recipes->executeAll(null, $variables);
        }
    }

    public static function start(Main $owner): void {
        $seconds = (int)date("s");
        $owner->getScheduler()->scheduleDelayedRepeatingTask(new CheckTimeTriggerTask(), 20 * (60 - $seconds), 20 * 60);
    }
}