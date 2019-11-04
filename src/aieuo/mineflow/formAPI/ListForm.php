<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\FormAPI\element\Button;

class ListForm extends Form {

    /** @var string */
    private $content = "";
    /** @var Button[] */
    private $buttons = [];

    /**
     * @param string $content
     * @return self
     */
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    /**
     * @param Button $button
     * @return self
     */
    public function addButton(Button $button): self {
        $this->buttons[] = $button;
        return $this;
    }

    /**
     * @param Button[] $buttons
     * @return self
     */
    public function addButtons(array $buttons): self {
        $this->buttons = array_merge($this->buttons, $buttons);
        return $this;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => "form",
            "title" => $this->checkTranslate($this->title),
            "content" => $this->checkTranslate($this->content),
            "buttons" => $this->buttons
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