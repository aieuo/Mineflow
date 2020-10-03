<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\level\Position;

interface PositionFlowItem {

    public function getPositionVariableName(string $name = ""): string;

    public function setPositionVariableName(string $position, string $name = ""): void;

    public function getPosition(Recipe $origin, string $name = ""): ?Position;

    public function throwIfInvalidPosition(?Position $position): void;
}