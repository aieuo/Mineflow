<?php

namespace aieuo\mineflow\formAPI\element;

class Button extends Element {
    public function jsonSerialize(): array {
        return [
            "text" => str_replace("\\n", "\n", $this->checkTranslate($this->text)),
            "id" => $this->getUUId(),
        ];
    }
}