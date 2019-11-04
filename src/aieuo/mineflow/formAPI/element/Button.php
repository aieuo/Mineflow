<?php

namespace aieuo\mineflow\FormAPI\element;

class Button extends Element {
    public function jsonSerialize(): array {
        return [
            "text" => $this->checkTranslate($this->text)
        ];
    }
}