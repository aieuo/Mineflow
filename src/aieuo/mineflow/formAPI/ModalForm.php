<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\utils\Language;

class ModalForm extends Form {

    protected $type = self::MODAL_FORM;

    /** @var string */
    private $content = "";
    /** @var string */
    private $button1 = "";
    /** @var string */
    private $button2 = "";

    /**
     * @param string $content
     * @return self
     */
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * @param string $text
     * @return self
     */
    public function setButton1(string $text): self {
        $this->button1 = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getButton1(): string {
        return $this->button1;
    }

    /**
     * @param string $text
     * @return self
     */
    public function setButton2(string $text): self {
        $this->button2 = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getButton2(): string {
        return $this->button2;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => self::MODAL_FORM,
            "title" => Language::replace($this->title),
            "content" => str_replace("\\n", "\n", Language::replace($this->content)),
            "button1" => str_replace("\\n", "\n", Language::replace($this->button1)),
            "button2" => str_replace("\\n", "\n", Language::replace($this->button2))
        ];
        $form = $this->reflectErrors($form);
        return $form;
    }

    public function reflectErrors(array $form): array {
        if (!empty($this->messages)) {
            $form["content"] = implode("\n", array_keys($this->messages))."\n".$form["content"];
        }
        return $form;
    }
}