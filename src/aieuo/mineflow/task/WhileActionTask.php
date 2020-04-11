<?php

namespace aieuo\mineflow\task;

use aieuo\mineflow\flowItem\action\WhileTaskAction;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\scheduler\Task;

class WhileActionTask extends Task {

    /** @var WhileTaskAction */
    private $script;
    /** @var Recipe|null */
    private $recipe;
    /** @var int */
    private $count = 0;

    public function __construct(WhileTaskAction $script, ?Recipe $recipe) {
        $this->script = $script;
        $this->recipe = $recipe;
    }

    public function onRun(int $currentTick) {
        $this->count ++;
        $this->script->check($this->recipe);
    }
}