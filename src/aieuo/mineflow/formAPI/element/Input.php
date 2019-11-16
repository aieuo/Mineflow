<?php

namespace aieuo\mineflow\formAPI\element;

class Input extends Element {

    /** @var string */
    protected $type = "input";

    /** @var string */
    private $placeholder = "";
    /** @var string */
    private $default = "";

    public function __construct(string $text, string $placeholder = "", string $default = "") {
        parent::__construct($text);
        $this->placeholder = $placeholder;
        $this->default = $default;
    }

    /**
     * @param string $placeholder
     * @return self
     */
    public function setPlaceholder(string $placeholder): self {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string {
        return $this->placeholder;
    }

    /**
     * @param string $default
     * @return self
     */
    public function setDefault(string $default): self {
        $this->default = $default;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefault(): string {
        return $this->default;
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => $this->reflectHighlight($this->checkTranslate($this->text)),
            "placeholder" => $this->checkTranslate($this->placeholder),
            "default" => $this->checkTranslate($this->default),
        ];
    }
}