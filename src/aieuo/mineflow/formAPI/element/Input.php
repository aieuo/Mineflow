<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class Input extends Element {

    protected string $type = self::ELEMENT_INPUT;

    private string $placeholder;
    private string $default;

    private bool $required;
    private ?string $result;

    public function __construct(string $text, string $placeholder = "", string $default = "", bool $required = false, string &$result = null) {
        parent::__construct($text);
        $this->placeholder = $placeholder;
        $this->default = $default;

        $this->required = $required;
        $this->result = &$result;
    }

    public function setPlaceholder(string $placeholder): self {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getPlaceholder(): string {
        return $this->placeholder;
    }

    public function setDefault(string $default): self {
        $this->default = $default;
        return $this;
    }

    public function getDefault(): string {
        return $this->default;
    }

    public function isRequired(): bool {
        return $this->required;
    }

    public function setRequired(bool $required): self {
        $this->required = $required;
        return $this;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        $data = str_replace("\\n", "\n", $response->getInputResponse());
        $this->result = $data;

        if ($this->isRequired() and $data === "") {
            $response->addError("@form.insufficient");
        }

        if ($response->getInputResponse() !== $data) $response->overrideResponse($data);
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => Language::replace($this->extraText).$this->reflectHighlight(Language::replace($this->text)),
            "placeholder" => Language::replace($this->placeholder),
            "default" => str_replace("\n", "\\n", Language::replace($this->default)),
            "mineflow" => $this->serializeExtraData(),
        ];
    }

    public function serializeExtraData(): array {
        return [
            "type" => "text",
            "required" => $this->isRequired(),
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"])) return null;

        if (isset($data["mineflow"]["type"]) and $data["mineflow"]["type"] === "number") {
            return NumberInput::fromSerializedArray($data);
        }

        return new Input($data["text"], $data["placeholder"], $data["default"] ?? "", $data["mineflow"]["required"] ?? false);
    }
}