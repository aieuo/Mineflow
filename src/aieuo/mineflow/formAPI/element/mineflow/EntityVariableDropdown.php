<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;

class EntityVariableDropdown extends VariableDropdown {

    protected string $variableClass = EntityObjectVariable::class;

    protected array $actions = [
        FlowItemIds::GET_ENTITY,
        FlowItemIds::CREATE_HUMAN_ENTITY,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.entity", $variables, [
            PlayerObjectVariable::class,
            EntityObjectVariable::class
        ], $default, $optional);
    }
}