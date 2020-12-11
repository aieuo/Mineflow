<?php

namespace aieuo\mineflow\formAPI\element;


use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class CancelToggle extends Toggle {

    private $onCancel;

    public function __construct(?callable $callback = null, string $text = "@form.cancelAndBack", bool $default = false) {
        $this->onCancel = $callback;
        parent::__construct($text, $default);
    }

    public function getOnCancel(): ?callable {
        return $this->onCancel;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        if ($response->getToggleResponse()) {
            $response->ignoreResponse();
            if (is_callable($this->getOnCancel())) {
                ($this->getOnCancel())();
                $response->setInterruptCallback(function () { return true; });
            }
        }
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "default" => $this->getDefault(),
            "mineflow" => [
                "type" => "cancelToggle"
            ]
        ];
    }
}