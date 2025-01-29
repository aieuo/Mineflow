<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\NumberInputPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\SliderPlaceholder;
use aieuo\mineflow\formAPI\element\Slider;
use aieuo\mineflow\formAPI\element\StringResponseDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\formAPI\utils\FormUtils;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\EvaluableString;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function is_callable;

class CustomForm extends Form {

    protected string $type = self::CUSTOM_FORM;

    /** @var Element[] */
    private array $contents = [];

    public function setContents(array $contents): self {
        $this->contents = $contents;
        return $this;
    }

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

    /**
     * @param Element[] $contents
     * @return $this
     */
    public function addContents(array $contents): self {
        $this->contents = array_merge($this->contents, $contents);
        return $this;
    }

    public function setContent(Element $element, int $index): self {
        $this->contents[$index] = $element;
        return $this;
    }

    public function removeContentAt(int $index): self {
        unset($this->contents[$index]);
        $this->contents = array_values($this->contents);
        return $this;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => "custom_form",
            "title" => Language::replace($this->title),
            "content" => $this->contents
        ];
        return $this->reflectErrors($form);
    }

    public function resetErrors(): Form {
        foreach ($this->getContents() as $content) {
            $content->setHighlight(null);
            $content->setExtraText("");
        }
        return parent::resetErrors();
    }

    public function reflectErrors(array $form): array {
        for ($i = 0, $iMax = count($form["content"]); $i < $iMax; $i++) {
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

    public function replaceVariablesFromExecutor(FlowItemExecutor $executor): self {
        $this->setTitle($executor->replaceVariables($this->getTitle()));
        foreach ($this->getContents() as $content) {
            $content->setText($executor->replaceVariables($content->getText()));
            if ($content instanceof SliderPlaceholder) {
                $content->setDefaultStr($executor->replaceVariables($content->getDefaultStr()));
                $content->setMinStr($executor->replaceVariables($content->getMinStr()));
                $content->setMaxStr($executor->replaceVariables($content->getMaxStr()));
                $content->setStepStr($executor->replaceVariables($content->getStepStr()));
            } elseif ($content instanceof NumberInputPlaceholder) {
                $content->setPlaceholder($executor->replaceVariables($content->getPlaceholder()));
                $content->setDefault($executor->replaceVariables($content->getDefault()));
                $content->setMinStr($content->getMinStr() === null ? null : $executor->replaceVariables($content->getMinStr()));
                $content->setMaxStr($content->getMaxStr() === null ? null : $executor->replaceVariables($content->getMaxStr()));
            } elseif ($content instanceof Input) {
                $content->setPlaceholder($executor->replaceVariables($content->getPlaceholder()));
                $content->setDefault($executor->replaceVariables($content->getDefault()));
            } elseif ($content instanceof Dropdown) {
                $options = [];
                foreach ($content->getOptions() as $option) {
                    foreach (FormUtils::expandText(new EvaluableString($option), $executor->getVariableRegistryCopy()) as $text) {
                        $options[] = $text;
                    }
                }
                $content->setOptions($options);
            }
        }
        return $this;
    }

    public function insufficient(int $index): void {
        $this->error([["@form.insufficient", $index]]);
    }

    public function error(array $errors = []): void {
        $this->resend($errors);
    }

    public function resend(array $errors = [], array $messages = [], array $responseOverrides = [], array $elementOverrides = []): void {
        if (empty($this->lastResponse) or !($this->lastResponse[0] instanceof Player) or !$this->lastResponse[0]->isOnline()) return;

        foreach ($elementOverrides as $i => $element) {
            $this->setContent($element, $i);
        }
        $this->setDefaultsFromResponse($this->lastResponse[1], $responseOverrides)
            ->resetErrors()
            ->addMessages($messages)
            ->addErrors($errors)
            ->show($this->lastResponse[0]);
    }

    public function onSubmit(Player $player, $data): void {
        if ($data !== null) {
            $response = new CustomFormResponse($this, $data);
            foreach ($this->getContents() as $i => $content) {
                $response->setCurrentIndex($i);
                $content->onFormSubmit($response, $player);
            }

            $callback = $response->getInterruptCallback();
            if (is_callable($callback) and $callback($response->isResponseIgnored())) return;

            if (!$response->isResponseIgnored()) {
                if ($response->shouldResendForm() or $response->hasError()) {
                    $this->resend($response->getErrors(), [], $response->getDefaultOverrides(), $response->getElementOverrides());
                    return;
                }
            }

            foreach ($response->getResponseOverrides() as $i => $override) {
                $data[$i] = $override;
            }
        }

        parent::onSubmit($player, $data);
    }

    private function setDefaultsFromResponse(array $data, array $overwrites): self {
        foreach ($this->getContents() as $i => $content) {
            if ($content instanceof Input or $content instanceof Slider or $content instanceof Toggle) {
                $content->setDefault($overwrites[$i] ?? $data[$i]);
            } elseif ($content instanceof StringResponseDropdown) {
                $content->setDefaultString($overwrites[$i] ?? $data[$i]);
            } elseif ($content instanceof Dropdown) {
                $content->setDefaultIndex($overwrites[$i] ?? $data[$i]);
            }
        }
        return $this;
    }

    public function __clone() {
        $elements = [];
        foreach ($this->getContents() as $i => $content) {
            $elements[$i] = clone $content;
        }
        $this->setContents($elements);
    }
}