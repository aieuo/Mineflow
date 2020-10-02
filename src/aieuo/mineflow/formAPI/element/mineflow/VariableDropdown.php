<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;

abstract class VariableDropdown extends Dropdown {

    /** @var string */
    protected $variableType;

    /** @var string */
    private $defaultText;

    public function __construct(string $text, array $variables = [], array $variableTypes = [], string $default = "") {
        $variables = array_filter($variables, function (DummyVariable $v) use ($variableTypes) {
            return in_array($v->getValueType(), $variableTypes);
        });
        $options = array_values(array_unique(array_map(function (DummyVariable $v) {
            return $v->getName();
        }, $variables)));

        $this->defaultText = $default;
        if ($default === "") {
            $defaultKey = 0;
        } else {
            if (!in_array($default, $options, true)) $options[] = $default;
            $defaultKey = array_search($default, $options, true);
        }

        $options[] = Language::get("form.element.variableDropdown.inputManually");

        parent::__construct($text, $options, $defaultKey);
    }

    public function getVariableType(): string {
        return $this->variableType;
    }

    public function getDefaultText(): string {
        return $this->defaultText;
    }
}