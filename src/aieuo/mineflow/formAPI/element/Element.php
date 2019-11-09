<?php

namespace aieuo\mineflow\FormAPI\element;

use aieuo\mineflow\utils\Language;

abstract class Element implements \JsonSerializable {

    /** @var string */
    protected $type;
    /** @var string */
    protected $text = "";

    public function __construct(string $text) {
        $this->text = $text;
    }

    /**
     * @param string $text
     * @return self
     */
    public function setText(string $text): self {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string {
        return $this->text;
    }

    /**
     * @param string $text
     * @return string
     */
    public function checkTranslate(string $text): string {
        $text = preg_replace_callback("/@([a-zA-Z.]+)/", function ($matches) {
            return Language::get($matches[1]);
        }, $text);
        return $text;
    }

    /**
     * @return array
     */
    abstract public function jsonSerialize(): array;
}