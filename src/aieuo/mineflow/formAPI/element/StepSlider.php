<?php

namespace aieuo\mineflow\formAPI\element;

class StepSlider extends Dropdown {

    /** @var string */
    protected $type = self::ELEMENT_STEP_SLIDER;

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => $this->checkTranslate($this->extraText).$this->reflectHighlight($this->checkTranslate($this->text)),
            "steps" => $this->options,
            "default" => $this->default,
        ];
    }
}