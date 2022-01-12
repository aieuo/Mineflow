<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\variable\DummyVariable;

class Vector3VariableDropdown extends VariableDropdown {

    protected string $variableType = DummyVariable::VECTOR3;

    protected array $actions = [
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.position", $variables, [
            DummyVariable::VECTOR3,
            DummyVariable::POSITION,
            DummyVariable::LOCATION,
            DummyVariable::PLAYER,
            DummyVariable::HUMAN,
            DummyVariable::LIVING,
            DummyVariable::ENTITY,
            DummyVariable::BLOCK,
        ], $default, $optional);
    }
}