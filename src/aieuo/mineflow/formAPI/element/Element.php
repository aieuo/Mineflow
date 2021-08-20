<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\response\CustomFormResponse;
use pocketmine\Player;
use pocketmine\utils\UUID;

abstract class Element implements \JsonSerializable {

    public const ELEMENT_LABEL = "label";
    public const ELEMENT_INPUT = "input";
    public const ELEMENT_SLIDER = "slider";
    public const ELEMENT_STEP_SLIDER = "step_slider";
    public const ELEMENT_DROPDOWN = "dropdown";
    public const ELEMENT_TOGGLE = "toggle";

    protected string $type;
    /** @var string */
    protected $text = "";
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

        switch ($data["type"]) {
            case self::ELEMENT_LABEL:
                return Label::fromSerializedArray($data);
            case self::ELEMENT_TOGGLE:
                return Toggle::fromSerializedArray($data);
            case self::ELEMENT_INPUT:
                return Input::fromSerializedArray($data);
            case self::ELEMENT_SLIDER:
                return Slider::fromSerializedArray($data);
            case self::ELEMENT_STEP_SLIDER:
                return StepSlider::fromSerializedArray($data);
            case self::ELEMENT_DROPDOWN:
                return Dropdown::fromSerializedArray($data);
            default:
                return null;
        }
    }
}