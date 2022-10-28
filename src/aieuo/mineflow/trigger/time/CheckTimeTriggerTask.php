<?php

namespace aieuo\mineflow\trigger\time;

use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\scheduler\Task;

class CheckTimeTriggerTask extends Task {

    private TriggerHolder $triggerHolder;

    public function __construct() {
        $this->triggerHolder = TriggerHolder::getInstance();
    }

    public function onRun(): void {
        $date = new \DateTime("now", Mineflow::getTimeTriggerTimeZone());
        $trigger = TimeTrigger::create($date->format("H"), $date->format("i"));
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
