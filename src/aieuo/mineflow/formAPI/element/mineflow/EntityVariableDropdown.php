<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class EntityVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::ENTITY;

    protected $actions = [
        FlowItemIds::GET_ENTITY,
        FlowItemIds::CREATE_HUMAN_ENTITY,
    ];

    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.entity", $variables, [DummyVariable::PLAYER, DummyVariable::ENTITY], $default, $optional);
    }
}