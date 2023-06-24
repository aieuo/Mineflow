<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\math\AxisAlignedBB;

#[Deprecated]
/**
 * @see AxisAlignedBBArgument
 */
interface AxisAlignedBBFlowItem {

    public function getAxisAlignedBBVariableName(string $name = ""): string;

    public function setAxisAlignedBBVariableName(string $vector3, string $name = ""): void;

    /**
     * @param FlowItemExecutor $source
     * @param string $name
     * @return AxisAlignedBB
     * @throws InvalidFlowValueException
     */
    public function getAxisAlignedBB(FlowItemExecutor $source, string $name = ""): AxisAlignedBB;

}