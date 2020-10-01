<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\variable\DummyVariable;

class EntityVariableDropdown extends VariableDropdown {

    public function __construct(array $variables = [], string $default = "") {
        parent::__construct("@flowItem.form.target.entity", $variables, [DummyVariable::PLAYER, DummyVariable::ENTITY], $default);
    }
}