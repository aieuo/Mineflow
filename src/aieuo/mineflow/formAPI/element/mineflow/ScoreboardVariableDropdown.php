<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class ScoreboardVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::SCOREBOARD;

    protected $actions = [
        FlowItemIds::CREATE_CONFIG_VARIABLE,
    ];

    public function __construct(array $variables = [], string $default = "") {
        parent::__construct("@flowItem.form.target.scoreboard", $variables, [DummyVariable::SCOREBOARD], $default);
    }
}