<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class ItemVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::ITEM;

    protected $actions = [
        FlowItemIds::CREATE_ITEM_VARIABLE,
    ];

    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.item", $variables, [DummyVariable::ITEM], $default, $optional);
    }
}