<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\variable\DummyVariable;

class BlockVariableDropdown extends VariableDropdown {

    public function __construct(array $variables = [], string $default = "") {
        parent::__construct("@flowItem.form.target.block", $variables, [DummyVariable::BLOCK], $default);
    }
}