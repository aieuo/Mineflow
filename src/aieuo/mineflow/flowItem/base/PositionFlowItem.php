<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\level\Position;

interface PositionFlowItem {

    public function getPositionVariableName(string $name = ""): string;

    public function setPositionVariableName(string $position, string $name = ""): void;

    /**
     * @param Recipe $source
     * @param string $name
     * @return Position
     * @throws InvalidFlowValueException
     */
    public function getPosition(Recipe $source, string $name = ""): Position;
}