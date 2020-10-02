<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\utils\Language;

class ExampleInput extends Input {

    public function __construct(string $text, string $example = "", string $default = "", bool $required = false) {
        parent::__construct($text, Language::get("form.example", [$example]), $default, $required);
    }
}