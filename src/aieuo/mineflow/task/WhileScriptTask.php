<?php

namespace aieuo\mineflow\task;

use aieuo\mineflow\action\script\WhileScript;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\entity\Entity;
use pocketmine\scheduler\Task;

class WhileScriptTask extends Task {

    /** @var WhileScript */
    private $script;
    /** @var Entity|null */
    private $entity;
    /** @var Recipe|null */
    private $recipe;
    /** @var int */
    private $count = 0;

    public function __construct(WhileScript $script, ?Entity $entity, ?Recipe $recipe) {
        $this->script = $script;
        $this->entity = $entity;
        $this->recipe = $recipe;
    }

    public function onRun(int $currentTick) {
        $this->count ++;
        $this->script->check($this->entity, $this->recipe);
    }
}