<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\response\CustomFormResponse;
use pocketmine\Player;

abstract class Element implements \JsonSerializable {

    public const ELEMENT_LABEL = "label";
    public const ELEMENT_INPUT = "input";
    public const ELEMENT_SLIDER = "slider";
    public const ELEMENT_STEP_SLIDER = "step_slider";
    public const ELEMENT_DROPDOWN = "dropdown";
    public const ELEMENT_TOGGLE = "toggle";

    protected string $type;
    protected string $text = "";
    protected string $extraText = "";
    protected ?string $highlight = "";

    public function __construct(string $text) {
        $this->text = str_replace("\\n", "\n", $text);
    }

    public function setText(string $text): self {
        $this->text = str_replace("\\n", "\n", $text);
        return $this;
    }

    public function setExtraText(string $extraText): self {
        $this->extraText = $extraText;
        return $this;
    }

    public function getText(): string {
        return $this->text;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setHighlight(?string $color): void {
        $this->highlight = $color;
    }

    public function reflectHighlight(string $text): string {
        if (empty($this->highlight)) return $text;
        return $this->highlight.preg_replace("/§[a-f0-9]/u", "", $text);
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
    }

    abstract public function jsonSerialize(): array;

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["type"]) or !isset($data["text"])) return null;

        return match ($data["type"]) {
            self::ELEMENT_LABEL => Label::fromSerializedArray($data),
            self::ELEMENT_TOGGLE => Toggle::fromSerializedArray($data),
            self::ELEMENT_INPUT => Input::fromSerializedArray($data),
            self::ELEMENT_SLIDER => Slider::fromSerializedArray($data),
            self::ELEMENT_STEP_SLIDER => StepSlider::fromSerializedArray($data),
            self::ELEMENT_DROPDOWN => Dropdown::fromSerializedArray($data),
            default => null,
        };
    }
}