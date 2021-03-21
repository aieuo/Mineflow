<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\entity\Entity;

interface EntityFlowItem {

    public function getEntityVariableName(string $name = ""): string;

    public function setEntityVariableName(string $entity, string $name = ""): void;

    /**
     * @param Recipe $source
     * @param string $name
     * @return Entity
     * @throws InvalidFlowValueException
     */
    public function getEntity(Recipe $source, string $name = ""): Entity;

    public function throwIfInvalidEntity(Entity $entity): void;
}