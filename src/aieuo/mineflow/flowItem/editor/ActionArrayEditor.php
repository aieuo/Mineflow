<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\editor;

use aieuo\mineflow\flowItem\argument\FlowItemArrayArgument;

class ActionArrayEditor extends FlowItemArrayEditor {

    public function __construct(FlowItemArrayArgument $argument, string $buttonText = "@action.edit", bool $primary = false) {
        parent::__construct($argument, $buttonText, $primary);
    }
}