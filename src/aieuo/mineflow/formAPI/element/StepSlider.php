<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\utils\Language;

class StepSlider extends Dropdown {

    /** @var string */
    protected $type = self::ELEMENT_STEP_SLIDER;

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "steps" => $this->options,
            "default" => $this->default,
        ];
    }
}