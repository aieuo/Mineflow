<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\math\Vector3;

interface Vector3FlowItem {

    public function getVector3VariableName(string $name = ""): string;

    public function setVector3VariableName(string $vector3, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getVector3(FlowItemExecutor $source, string $name = ""): Vector3;

}