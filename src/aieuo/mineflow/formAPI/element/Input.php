<?php

namespace aieuo\mineflow\formAPI\element;

class Input extends Element {

    /** @var string */
    protected $type = self::ELEMENT_INPUT;

    /** @var string */
    private $placeholder;
    /** @var string */
    private $default;

    /** @var bool */
    private $required;

    public function __construct(string $text, string $placeholder = "", string $default = "", bool $required = false) {
        parent::__construct($text);
        $this->placeholder = $placeholder;
        $this->default = $default;

        $this->required = $required;
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

    public function isRequired(): bool {
        return $this->required;
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => $this->checkTranslate($this->extraText).$this->reflectHighlight($this->checkTranslate($this->text)),
            "placeholder" => $this->checkTranslate($this->placeholder),
            "default" => $this->checkTranslate($this->default),
        ];
    }
}