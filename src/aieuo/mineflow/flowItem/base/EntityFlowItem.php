<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\entity\Entity;

#[Deprecated]
/**
 * @see EntityArgument
 */
interface EntityFlowItem {

    public function getEntityVariableName(string $name = ""): string;

    public function setEntityVariableName(string $entity, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getEntity(FlowItemExecutor $source, string $name = ""): Entity;

    /** @throws InvalidFlowValueException */
    public function getOnlineEntity(FlowItemExecutor $source, string $name = ""): Entity;

}
