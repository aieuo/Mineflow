<?php

declare(strict_types=1);

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use aieuo\mineflow\variable\object\LivingVariable;
use aieuo\mineflow\variable\object\PlayerVariable;

class EntityVariableDropdown extends VariableDropdown {

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
            PlayerVariable::class,
            HumanVariable::class,
            LivingVariable::class,
            EntityVariable::class,
        ], $default, $optional);
    }
}