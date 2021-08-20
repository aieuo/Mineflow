<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\utils\Language;

class Toggle extends Element {

    protected string $type = self::ELEMENT_TOGGLE;

    private bool $default;

    public function __construct(string $text, bool $default = false) {
        parent::__construct($text);
        $this->default = $default;
    }

    public function setDefault(bool $default): self {
        $this->default = $default;
        return $this;
    }

    public function getDefault(): bool {
        return $this->default;
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "default" => $this->default,
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"])) return null;

        if (isset($data["mineflow"]["type"]) and $data["mineflow"]["type"] === "cancelToggle") {
            return CancelToggle::fromSerializedArray($data);
        }

        return new Toggle($data["text"], $data["default"] ?? false);
    }
}