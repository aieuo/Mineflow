<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class PositionVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::POSITION;

    protected $actions = [
        FlowItemIds::CREATE_POSITION_VARIABLE,
        FlowItemIds::GET_ENTITY_SIDE,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.position", $variables, [DummyVariable::POSITION, DummyVariable::LOCATION, DummyVariable::PLAYER, DummyVariable::ENTITY, DummyVariable::BLOCK], $default, $optional);
    }
}