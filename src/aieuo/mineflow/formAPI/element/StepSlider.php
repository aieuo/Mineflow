<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\utils\Language;

class StepSlider extends Dropdown {

    protected string $type = self::ELEMENT_STEP_SLIDER;

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "steps" => $this->options,
            "default" => $this->default,
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["steps"])) return null;

        return new StepSlider($data["text"], $data["steps"], $data["default"] ?? 0);
    }
}