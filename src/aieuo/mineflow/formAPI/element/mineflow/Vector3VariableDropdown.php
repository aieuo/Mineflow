<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockObjectVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\LocationObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use aieuo\mineflow\variable\object\Vector3ObjectVariable;

class Vector3VariableDropdown extends VariableDropdown {

    protected string $variableClass = Vector3ObjectVariable::class;

    protected array $actions = [
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct(
            $text ?? "@action.form.target.position",
            $variables,
            [
                Vector3ObjectVariable::class,
                PositionObjectVariable::class,
                LocationObjectVariable::class,
                PlayerObjectVariable::class,
                EntityObjectVariable::class,
                BlockObjectVariable::class
            ],
            $default,
            $optional
        );
    }
}