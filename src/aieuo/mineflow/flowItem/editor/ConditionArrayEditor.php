<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\editor;

use aieuo\mineflow\flowItem\argument\FlowItemArrayArgument;

class ConditionArrayEditor extends FlowItemArrayEditor {

    public function __construct(FlowItemArrayArgument $argument, string $buttonText = "@condition.edit", bool $primary = false) {
        parent::__construct($argument, $buttonText, $primary);
    }
}