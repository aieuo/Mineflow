<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use function is_callable;

class ModalForm extends Form {

    protected string $type = self::MODAL_FORM;

    private string $content = "";
    private string $button1 = "@form.yes";
    private string $button2 = "@form.no";

    /** @var callable|null */
    private $button1Click;
    /** @var callable|null */
    private $button2Click;

    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setButton1(string $text, callable $onClick = null): self {
        $this->button1 = $text;
        $this->button1Click = $onClick;
        return $this;
    }

    public function onYes(callable $onClick): self {
        $this->button1Click = $onClick;
        return $this;
    }

    public function getButton1Text(): string {
        return $this->button1;
    }

    public function setButton2(string $text, callable $onClick = null): self {
        $this->button2 = $text;
        $this->button2Click = $onClick;
        return $this;
    }

    public function onNo(callable $onClick): self {
        $this->button2Click = $onClick;
        return $this;
    }

    public function setButton(int $index, string $text, callable $onClick = null): self {
        if ($index === 1) {
            $this->setButton1($text, $onClick);
        } else {
            $this->setButton2($text, $onClick);
        }
        return $this;
    }

    public function getButton2Text(): string {
        return $this->button2;
    }

    public function getButtonText(int $index): string {
        return $index === 1 ? $this->getButton1Text() : $this->getButton2Text();
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => self::MODAL_FORM,
            "title" => Language::replace($this->title),
            "content" => str_replace("\\n", "\n", Language::replace($this->content)),
            "button1" => str_replace("\\n", "\n", Language::replace($this->button1)),
            "button2" => str_replace("\\n", "\n", Language::replace($this->button2))
        ];
        return $this->reflectErrors($form);
    }

    public function reflectErrors(array $form): array {
        if (!empty($this->messages)) {
            $form["content"] = implode("\n", array_keys($this->messages))."\n".$form["content"];
        }
        return $form;
    }

    public function replaceVariablesFromExecutor(FlowItemExecutor $executor): self {
        $this->setTitle($executor->replaceVariables($this->getTitle()));
        $this->setContent($executor->replaceVariables($this->getContent()));
        $this->setButton1($executor->replaceVariables($this->getButton1Text()));
        $this->setButton2($executor->replaceVariables($this->getButton2Text()));
        return $this;
    }

    public function onSubmit(Player $player, $data): void {
        if ($data === null) {
            parent::onSubmit($player, $data);
            return;
        }

        $onClick = $data ? $this->button1Click : $this->button2Click;
        if (is_callable($onClick)) {
            $onClick($player);
            return;
        }

        parent::onSubmit($player, $data);
    }
}