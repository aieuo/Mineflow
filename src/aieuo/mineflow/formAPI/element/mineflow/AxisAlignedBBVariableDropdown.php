<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class AxisAlignedBBVariableDropdown extends VariableDropdown {

    protected string $variableType = DummyVariable::AXIS_ALIGNED_BB;

    protected array $actions = [
        FlowItemIds::CREATE_AABB,
        FlowItemIds::CREATE_AABB_BY_VECTOR3_VARIABLE,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.aabb", $variables, [
            DummyVariable::AXIS_ALIGNED_BB,
        ], $default, $optional);
    }
}