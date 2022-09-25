<?php

declare(strict_types=1);

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;

class ScoreboardVariableDropdown extends VariableDropdown {

    protected string $variableClass = ScoreboardVariable::class;

    protected array $actions = [
        FlowItemIds::CREATE_CONFIG_VARIABLE,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.scoreboard", $variables, [
            ScoreboardVariable::class,
        ], $default, $optional);
    }
}
