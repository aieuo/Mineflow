<?php

declare(strict_types=1);

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use aieuo\mineflow\variable\object\PlayerVariable;

class HumanVariableDropdown extends VariableDropdown {

    protected array $actions = [
        FlowItemIds::GET_PLAYER
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.human", $variables, [
            PlayerVariable::class,
            HumanVariable::class,
        ], $default, $optional);
    }
}