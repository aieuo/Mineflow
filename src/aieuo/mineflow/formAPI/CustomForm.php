<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\VariableDropdown;
use aieuo\mineflow\formAPI\element\NumberInput;
use aieuo\mineflow\formAPI\element\Slider;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
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

    public function setContent(Element $element, int $index): self {
        $this->contents[$index] = $element;
        return $this;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => "custom_form",
            "title" => Language::replace($this->title),
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
        for ($i = 0; $i < count($form["content"]); $i++) {
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

    public function resend(array $errors = [], array $messages = [], array $overwrites = []): void {
        if (empty($this->lastResponse) or !($this->lastResponse[0] instanceof Player) or !$this->lastResponse[0]->isOnline()) return;

        $this->setDefaultsFromResponse($this->lastResponse[1], $overwrites)
            ->resetErrors()
            ->addMessages($messages)
            ->addErrors($errors)
            ->show($this->lastResponse[0]);
    }

    public function handleResponse(Player $player, $data): void {
        $this->lastResponse = [$player, $data];
        if ($data !== null) {
            $errors = [];
            $isCanceled = false;
            $resend = false;
            $overwrites = [];
            $addVariableDropdowns = [null, 0];
            foreach ($this->getContents() as $i => $content) {
                if ($content instanceof Input) {
                    $data[$i] = str_replace("\\n", "\n", $data[$i]);

                    if ($content->isRequired() and $data[$i] === "") {
                        $errors[] = ["@form.insufficient", $i];
                        continue;
                    }

                    if ($content instanceof NumberInput) {
                        if (($containsVariable = Main::getVariableHelper()->containsVariable($data[$i]))) continue;

                        if (!is_numeric($data[$i])) {
                            $errors[] = ["@flowItem.error.notNumber", $i];
                        } elseif (($min = $content->getMin()) !== null and (float)$data[$i] < $min) {
                            $errors[] = [Language::get("flowItem.error.lessValue", [$min]), $i];
                        } elseif (($max = $content->getMax()) !== null and (float)$data[$i] > $max) {
                            $errors[] = [Language::get("flowItem.error.overValue", [$max]), $i];
                        } elseif (($excludes = $content->getExcludes()) !== null and in_array((float)$data[$i], $excludes)) {
                            $errors[] = [Language::get("flowItem.error.excludedNumber", [implode(",", $excludes)]), $i];
                        }
                    }
                } elseif ($content instanceof CancelToggle and $data[$i]) {
                    $isCanceled = true;
                } elseif ($content instanceof VariableDropdown) {
                    $options = $content->getOptions();
                    $maxIndex = count($options) - 1;

                    if ($data[$i] === $maxIndex - 1) { // 手動で入力する時
                        $resend = true;
                        $overwrites[$i] = $content->getDefaultText();
                        $this->setContent(new ExampleInput($content->getText(), $content->getVariableType(), $content->getDefaultText(), true), $i);
                    } elseif ($data[$i] === $maxIndex) { // 変数を追加するとき
                        $addVariableDropdowns = [$content, $i];
                    } else { // 変数名を選択したとき
                        $data[$i] = explode(VariableDropdown::VALUE_SEPARATOR_LEFT, $options[$data[$i]])[0]; // TODO: 文字列操作せずに変数名だけ取り出す
                    }
                }
            }

            if (!$isCanceled and $addVariableDropdowns[0] instanceof VariableDropdown) {
                $addVariableDropdowns[0]->sendAddVariableForm($player, $this, $addVariableDropdowns[1]);
                return;
            }
            if ($resend or (!$isCanceled and !empty($errors))) {
                $this->resend($errors, [], $overwrites);
                return;
            }
        }

        parent::handleResponse($player, $data);
    }

    private function setDefaultsFromResponse(array $data, array $overwrites): self {
        foreach ($this->getContents() as $i => $content) {
            if ($content instanceof Input or $content instanceof Slider or $content instanceof Dropdown or $content instanceof Toggle) {
                $content->setDefault($overwrites[$i] ?? $data[$i]);
            }
        }
        return $this;
    }
}