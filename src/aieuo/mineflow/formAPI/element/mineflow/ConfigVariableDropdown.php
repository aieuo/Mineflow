<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class ConfigVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::CONFIG;

    protected $actions = [
        FlowItemIds::CREATE_CONFIG_VARIABLE,
    ];

    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.config", $variables, [DummyVariable::CONFIG], $default, $optional);
    }
}