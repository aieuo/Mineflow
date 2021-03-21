<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Entity;

interface EntityFlowItem {

    public function getEntityVariableName(string $name = ""): string;

    public function setEntityVariableName(string $entity, string $name = ""): void;

    /**
     * @param FlowItemExecutor $source
     * @param string $name
     * @return Entity
     * @throws InvalidFlowValueException
     */
    public function getEntity(FlowItemExecutor $source, string $name = ""): Entity;

    public function throwIfInvalidEntity(Entity $entity): void;
}