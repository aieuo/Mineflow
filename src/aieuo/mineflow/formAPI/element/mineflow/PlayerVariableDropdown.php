<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class PlayerVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::PLAYER;

    protected $actions = [
        FlowItemIds::GET_PLAYER
    ];

    public function __construct(array $variables = [], string $default = "", string $text = "@action.form.target.player") {
        parent::__construct($text, $variables, [DummyVariable::PLAYER], $default);
    }
}