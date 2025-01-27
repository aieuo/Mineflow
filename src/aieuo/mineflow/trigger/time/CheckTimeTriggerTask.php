<?php

namespace aieuo\mineflow\trigger\time;

use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\scheduler\Task;
use function microtime;

class CheckTimeTriggerTask extends Task {

    public function onRun(): void {
        $date = new \DateTime("now", Mineflow::getTimeZone());
        $trigger = new TimeTrigger((int)$date->format("H"), (int)$date->format("i"));
        $variables = $trigger->getVariables((int)microtime(true));
        
        TriggerHolder::executeRecipeAll($trigger, null, $variables, null);
    }

    public static function start(Main $owner): void {
        $seconds = (int)date("s");
        $owner->getScheduler()->scheduleDelayedRepeatingTask(new CheckTimeTriggerTask(), 20 * (60 - $seconds), 20 * 60);
    }
}