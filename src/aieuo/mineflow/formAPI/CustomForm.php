<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use pocketmine\Player;
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

    public function resetErrors(): Form {
        foreach ($this->getContents() as $content) {
            $content->setHighlight(null);
            $content->setExtraText("");
        }
        return parent::resetErrors();
    }

    public function reflectErrors(array $form): array {
        for ($i=0; $i<count($form["content"]); $i++) {
            if (empty($this->highlights[$i])) continue;
            /** @var Element $content */
            $content = $form["content"][$i];
            $content->setHighlight(TextFormat::YELLOW);
        }
        if (!empty($this->messages) and !empty($this->contents)) {
            $form["content"][0]->setExtraText(implode("\n", array_keys($this->messages))."\n");
        }
        return $form;
    }

    public function handleResponse(Player $player, $data): void {
        if ($data!== null) {
            foreach ($this->getContents() as $i => $content) {
                if ($content instanceof Input) {
                    $data[$i] = str_replace("\\n", "\n", $data[$i]);
                }
            }
        }

        parent::handleResponse($player, $data);
    }
}