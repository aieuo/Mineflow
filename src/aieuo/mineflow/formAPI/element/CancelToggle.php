<?php

namespace aieuo\mineflow\formAPI\element;


class CancelToggle extends Toggle {

    private $onCancel;

    public function __construct(?callable $callback = null, string $text = "@form.cancelAndBack", bool $default = false) {
        $this->onCancel = $callback;
        parent::__construct($text, $default);
    }

    public function getOnCancel(): ?callable {
        return $this->onCancel;
    }
}