<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\variable\DummyVariable;

class ConfigVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::CONFIG;

    public function __construct(array $variables = [], string $default = "") {
        parent::__construct("@flowItem.form.target.config", $variables, [DummyVariable::CONFIG], $default);
    }
}