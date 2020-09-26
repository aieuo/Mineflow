<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\NumberInput;
use aieuo\mineflow\utils\Language;

class ExampleNumberInput extends NumberInput {

    public function __construct(string $text, string $example = "", string $default = "", bool $required = false, ?float $min = null, ?float $max = null, array $excludes = []) {
        parent::__construct($text, Language::get("form.example", [$example]), $default, $required, $min, $max, $excludes);
    }
}