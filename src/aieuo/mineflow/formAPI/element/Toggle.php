<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class Toggle extends Element {

    protected string $type = self::ELEMENT_TOGGLE;

    private ?bool $result;

    public function __construct(
        string       $text,
        private bool $default = false,
        bool         &$result = null
    ) {
        parent::__construct($text);

        $this->result = &$result;
    }

    public function setDefault(bool $default): self {
        $this->default = $default;
        return $this;
    }

    public function getDefault(): bool {
        return $this->default;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        $this->result = $response->getToggleResponse();
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "default" => $this->default,
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"])) return null;

        if (isset($data["mineflow"]["type"]) and $data["mineflow"]["type"] === "cancelToggle") {
            return CancelToggle::fromSerializedArray($data);
        }

        return new Toggle($data["text"], $data["default"] ?? false);
    }
}