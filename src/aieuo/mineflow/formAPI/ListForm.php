<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\utils\FormUtils;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use function call_user_func;
use function is_callable;

class ListForm extends Form {

    protected string $type = self::LIST_FORM;

    private string $content = "@form.selectButton";
    /** @var Button[] */
    private array $buttons = [];

    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

	public function appendContent(string $content, bool $newLine = true): self {
		$this->content .= ($newLine ? "\n" : "").$content;
		return $this;
	}

    public function getContent(): string {
        return $this->content;
    }

    public function addButton(Button $button, bool $if = true): self {
        if ($if) $this->buttons[] = $button;
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

    /**
     * @param Button[] $buttons
     * @return self
     */
    public function setButtons(array $buttons): self {
        $this->buttons = $buttons;
        return $this;
    }

    public function addButtonsEach(array $inputs, callable $convert): self {
        foreach ($inputs as $i => $input) {
            $this->addButton($convert($input, $i));
        }
        return $this;
    }

    public function removeButton(int $index): self {
        unset($this->buttons[$index]);
        $this->buttons = array_values($this->buttons);
        return $this;
    }

    /**
     * @return Button[]
     */
    public function getButtons(): array {
        return $this->buttons;
    }

    public function getButton(int $index): ?Button {
        return $this->buttons[$index] ?? null;
    }

    public function getButtonByUUID(string $uuid): ?Button {
        foreach ($this->getButtons() as $button) {
            if ($button->getUUID() === $uuid) return $button;
        }
        return null;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => "form",
            "title" => Language::replace($this->title),
            "content" => str_replace("\\n", "\n", Language::replace($this->content)),
            "buttons" => $this->buttons
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
        $buttons = FormUtils::expandListFormButtons($this, $executor->getVariableRegistryCopy());
        $this->setButtons($buttons);
        return $this;
    }

    public function onSubmit(Player $player, $data): void {
        if ($data === null) {
            parent::onSubmit($player, $data);
            return;
        }

        $button = $this->getButton($data);
        if ($button !== null and is_callable($button->getOnClick())) {
            call_user_func($button->getOnClick(), $player);
            if ($button->skipIfCallOnClick) return;
        }

        parent::onSubmit($player, $data);
    }

    public function __clone() {
        $buttons = [];
        foreach ($this->getButtons() as $i => $button) {
            $buttons[$i] = (clone $button)->uuid($button->getUUID());
        }
        $this->setButtons($buttons);
    }
}