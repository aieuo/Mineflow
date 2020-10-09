<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class ModalForm extends Form {

    protected $type = self::MODAL_FORM;

    /** @var string */
    private $content = "";
    /** @var string */
    private $button1 = "";
    /** @var string */
    private $button2 = "";

    /** @var callable */
    private $button1Click;
    /** @var callable */
    private $button2Click;

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
     * @param callable|null $onClick
     * @return self
     */
    public function setButton1(string $text, callable $onClick = null): self {
        $this->button1 = $text;
        $this->button1Click = $onClick;
        return $this;
    }

    /**
     * @return string
     */
    public function getButton1Text(): string {
        return $this->button1;
    }

    /**
     * @param string $text
     * @param callable|null $onClick
     * @return self
     */
    public function setButton2(string $text, callable $onClick = null): self {
        $this->button2 = $text;
        $this->button2Click = $onClick;
        return $this;
    }

    /**
     * @return string
     */
    public function getButton2Text(): string {
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

    public function handleResponse(Player $player, $data): void {
        $this->lastResponse = [$player, $data];
        if ($data === null) {
            parent::handleResponse($player, $data);
            return;
        }

        $onClick = $data ? $this->button1Click : $this->button2Click;
        if (is_callable($onClick)) {
            $onClick($player);
            return;
        }

        $this->handleResponse($player, $data);
    }
}