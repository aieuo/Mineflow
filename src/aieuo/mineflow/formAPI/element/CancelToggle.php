<?php

namespace aieuo\mineflow\formAPI\element;


use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class CancelToggle extends Toggle {

    private $onCancel;
    private ?bool $result;

    public function __construct(?callable $callback = null, string $text = "@form.cancelAndBack", bool $default = false, bool &$result = null) {
        parent::__construct($text, $default);

        $this->onCancel = $callback;
        $this->result = &$result;
    }

    public function getOnCancel(): ?callable {
        return $this->onCancel;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        $this->result = $response->getToggleResponse();
        if ($response->getToggleResponse()) {
            $response->ignoreResponse();
            if (is_callable($this->getOnCancel())) {
                ($this->getOnCancel())();
                $response->setInterruptCallback(fn() => true);
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

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"])) return null;

        return new CancelToggle(null, $data["text"], $data["default"] ?? false);
    }
}