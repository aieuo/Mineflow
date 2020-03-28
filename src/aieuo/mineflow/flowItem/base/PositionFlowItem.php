<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\level\Position;

interface PositionFlowItem {

    public function getPositionVariableName(): string;

    public function setPositionVariableName(string $name);

    public function getPosition(Recipe $origin): ?Position;

    public function throwIfInvalidPosition(?Position $position);
}