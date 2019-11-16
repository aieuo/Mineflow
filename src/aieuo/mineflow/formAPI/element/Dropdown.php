<?php

namespace aieuo\mineflow\FormAPI\element;

class Dropdown extends Element {
    /** @var string */
    protected $type = "dropdown";

    /** @var array */
    protected $options = [];
    /** @var int */
    protected $default = 0;

    public function __construct(string $text, array $options = [], int $default = 0) {
        parent::__construct($text);
        $this->options = $options;
        $this->default = $default;
    }

    /**
     * @param string $options
     * @return self
     */
    public function addOption(string $option): self {
        $this->options[] = $option;
        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array {
        return $this->options;
    }

    /**
     * @param int $default
     * @return self
     */
    public function setDefault(int $default): self {
        $this->default = $default;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefault(): int {
        return $this->default;
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => $this->reflectHighlight($this->checkTranslate($this->text)),
            "options" => $this->options,
            "default" => $this->default,
        ];
    }
}