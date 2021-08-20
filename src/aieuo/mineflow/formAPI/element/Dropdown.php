<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\utils\Language;

class Dropdown extends Element {
    protected string $type = self::ELEMENT_DROPDOWN;

    protected array $options = [];
    protected int $default = 0;

    public function __construct(string $text, array $options = [], int $default = 0) {
        parent::__construct($text);
        $this->options = $options;
        $this->default = $default;
    }

    public function addOption(string $option): self {
        $this->options[] = $option;
        return $this;
    }

    public function setOptions(array $options): self {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array {
        return $this->options;
    }

    public function setDefault(int $default): self {
        $this->default = $default >= 0 ? $default : 0;
        return $this;
    }

    public function getDefault(): int {
        return $this->default;
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "options" => $this->options,
            "default" => $this->default,
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["options"])) return null;

        return new Dropdown($data["text"], $data["options"], $data["default"] ?? 0);
    }
}