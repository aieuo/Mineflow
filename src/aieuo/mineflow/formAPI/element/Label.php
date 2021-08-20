<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\utils\Language;

class Label extends Element {

    protected string $type = self::ELEMENT_LABEL;

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"])) return null;

        return new Label($data["text"]);
    }
}