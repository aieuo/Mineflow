<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\world\Position;

#[Deprecated]
/**
 * @see PositionPlaceholder
 */
interface PositionFlowItem {

    public function getPositionVariableNames(): array;

    public function getPositionVariableName(string $name = ""): string;

    public function setPositionVariableName(string $position, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getPosition(FlowItemExecutor $source, string $name = ""): Position;
}
