<?php

namespace aieuo\mineflow\formAPI;

use pocketmine\utils\TextFormat;

class CustomForm extends Form {

    /** @var Element[] */
    private $contents = [];

    /**
     * @param array $contents
     * @return self
     */
    public function setContents(array $contents): self {
        $this->contents = $contents;
        return $this;
    }

    /**
     * @param Element $content
     * @return self
     */
    public function addContent(Element ...$content): self {
        $this->contents[] = $content;
        return $this;
    }

    /**
     * @param Element[] $contents
     * @return self
     */
    public function addContents(Element ...$contents): self {
        $this->contents = array_merge($this->contents, $contents);
        return $this;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => "custom_form",
            "title" => $this->checkTranslate($this->title),
            "content" => $this->contents
        ];
        $form = $this->reflectErrors($form);
        return $form;
    }

    public function reflectErrors(array $form): array {
        for ($i=0; $i<count($form["content"]); $i++) {
            if (empty($this->highlights[$i])) continue;
            $content = $form["content"][$i];
            $content->setHighlight(TextFormat::YELLOW);
        }
        if (!empty($this->messages) and !empty($this->contents)) {
            $form["content"][0]->setText(implode("\n", array_keys($this->messages))."\n".$form["content"][0]->getText());
        }
        return $form;
    }
}