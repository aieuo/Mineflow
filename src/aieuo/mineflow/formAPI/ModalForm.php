<?php

namespace aieuo\mineflow\formAPI;

class ModalForm extends Form {

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
     * @param string $text
     * @return self
     */
    public function setButton1(string $text): self {
        $this->button1 = $text;
        return $this;
    }

    /**
     * @param string $text
     * @return self
     */
    public function setButton2(string $text): self {
        $this->button2 = $text;
        return $this;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => self::MODAL_FORM,
            "title" => $this->checkTranslate($this->title),
            "content" => $this->checkTranslate($this->content),
            "button1" => $this->checkTranslate($this->button1),
            "button2" => $this->checkTranslate($this->button2)
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