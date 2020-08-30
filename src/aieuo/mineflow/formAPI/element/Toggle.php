<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\utils\Language;

class Toggle extends Element {

    /** @var string */
    protected $type = self::ELEMENT_TOGGLE;

    /** @var boolean */
    private $default;

    public function __construct(string $text, bool $default = false) {
        parent::__construct($text);
        $this->default = $default;
    }

    /**
     * @param boolean $default
     * @return self
     */
    public function setDefault(bool $default): self {
        $this->default = $default;
        return $this;
    }

    /**
     * @return boolean
     */
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
}