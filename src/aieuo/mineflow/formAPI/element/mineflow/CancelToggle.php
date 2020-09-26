<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Toggle;

class CancelToggle extends Toggle {

    public function __construct(string $text = "@form.cancelAndBack", bool $default = false) {
        parent::__construct($text, $default);
    }
}