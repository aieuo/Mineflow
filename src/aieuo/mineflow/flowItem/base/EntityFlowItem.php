<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\entity\Entity;

interface EntityFlowItem {

    public function getEntityVariableName(): String;

    public function setEntityVariableName(string $name);

    public function getEntity(Recipe $origin): ?Entity;

    public function throwIfInvalidEntity(?Entity $player);
}