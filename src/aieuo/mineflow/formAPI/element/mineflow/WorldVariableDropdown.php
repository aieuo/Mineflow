<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class WorldVariableDropdown extends VariableDropdown {

    protected array $actions = [
        FlowItemIds::GET_WORLD_BY_NAME,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.world", $variables, [
            DummyVariable::WORLD,
        ], $default, $optional);
    }
}