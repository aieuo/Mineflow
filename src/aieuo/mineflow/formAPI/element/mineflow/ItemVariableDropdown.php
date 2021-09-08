<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;

class ItemVariableDropdown extends VariableDropdown {

    protected string $variableClass = ItemObjectVariable::class;

    protected array $actions = [
        FlowItemIds::CREATE_ITEM_VARIABLE,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.item", $variables, [ItemObjectVariable::class], $default, $optional);
    }
}