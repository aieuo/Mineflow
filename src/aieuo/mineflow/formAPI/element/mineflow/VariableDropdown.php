<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\variable\DummyVariable;

abstract class VariableDropdown extends Dropdown {

    public function __construct(string $text, array $variables = [], array $variableTypes = [], string $default = "") {
        $variables = array_filter($variables, function (DummyVariable $v) use ($variableTypes) {
            return in_array($v->getValueType(), $variableTypes);
        });
        $options = array_values(array_unique(array_map(function (DummyVariable $v) {
            return $v->getName();
        }, $variables)));
        if ($default === "") {
            $defaultKey = 0;
        } else {
            if (!in_array($default, $options, true)) $options[] = $default;
            $defaultKey = array_search($default, $options, true);
        }

        parent::__construct($text, $options, $defaultKey);
    }
}