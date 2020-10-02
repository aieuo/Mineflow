<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\variable\DummyVariable;

class ItemVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::ITEM;

    public function __construct(array $variables = [], string $default = "") {
        parent::__construct("@flowItem.form.target.item", $variables, [DummyVariable::ITEM], $default);
    }
}