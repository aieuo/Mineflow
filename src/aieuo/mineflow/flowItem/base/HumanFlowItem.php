<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\entity\Human;

interface HumanFlowItem {

    public function getHumanVariableName(string $name = ""): string;

    public function setHumanVariableName(string $entity, string $name = ""): void;

    /**
     * @param FlowItemExecutor $source
     * @param string $name
     * @return Human
     * @throws InvalidFlowValueException
     */
    public function getHuman(FlowItemExecutor $source, string $name = ""): Human;

    /** @throws InvalidFlowValueException */
    public function getOnlineHuman(FlowItemExecutor $source, string $name = ""): Human;
}
