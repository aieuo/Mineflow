<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\formAPI\element\Element;
use pocketmine\utils\TextFormat;

class CustomForm extends Form {

    protected $type = self::CUSTOM_FORM;

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
     * @param bool $add
     * @return self
     */
    public function addContent(Element $content, bool $add = true): self {
        if ($add) $this->contents[] = $content;
        return $this;
    }

    /**
     * @return Element[]
     */
    public function getContents(): array {
        return $this->contents;
    }

    public function getContent(int $index): ?Element {
        return $this->contents[$index] ?? null;
    }

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