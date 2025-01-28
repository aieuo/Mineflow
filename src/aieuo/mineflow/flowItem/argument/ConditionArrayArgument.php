<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;

class ConditionArrayArgument extends FlowItemArrayArgument {

    /**
     * @param string $name
     * @param FlowItem[] $value
     * @param string $description
     */
    public function __construct(string $name, array $value = [], string $description = "") {
        parent::__construct($name, $value, $description, FlowItemContainer::CONDITION);
    }
}