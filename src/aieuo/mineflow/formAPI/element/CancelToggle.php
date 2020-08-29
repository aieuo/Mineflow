<?php

namespace aieuo\mineflow\formAPI\element;

class CancelToggle extends Toggle {

    public function __construct(string $text = "@form.cancelAndBack", bool $default = false) {
        parent::__construct($text, $default);
    }
}