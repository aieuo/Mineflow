<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\element\mineflow\NumberInputPlaceholder;
use aieuo\mineflow\formAPI\response\CustomFormResponse;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class NumberInput extends Input {

    private ?float $result;

    public function __construct(
        string         $text,
        string         $placeholder = "",
        string         $default = "",
        bool           $required = false,
        private ?float $min = null,
        private ?float $max = null,
        private array  $excludes = [],
        float         &$result = null
    ) {
        parent::__construct($text, $placeholder, $default, $required);

        $this->result = &$result;
    }

    public function setMin(?float $min): void {
        $this->min = $min;
    }

    public function getMin(): ?float {
        return $this->min;
    }

    public function setMax(?float $max): void {
        $this->max = $max;
    }

    public function getMax(): ?float {
        return $this->max;
    }

    public function setExcludes(array $exclude): void {
        $this->excludes = $exclude;
    }

    public function getExcludes(): array {
        return $this->excludes;
    }

    public function onFormSubmit(CustomFormResponse $response, Player $player): void {
        parent::onFormSubmit($response, $player);
        $data = $response->getInputResponse();

        if ($data === "" or Mineflow::getVariableHelper()->containsVariable($data)) return;

        if (!is_numeric($data)) {
            $response->addError(Language::get("action.error.notNumber", [$data]));
        } elseif (($min = $this->getMin()) !== null and (float)$data < $min) {
            $response->addError(Language::get("action.error.lessValue", [$min, $data]));
        } elseif (($max = $this->getMax()) !== null and (float)$data > $max) {
            $response->addError(Language::get("action.error.overValue", [$max, $data]));
        } elseif (($excludes = $this->getExcludes()) !== null and in_array((float)$data, $excludes, true)) {
            $response->addError(Language::get("action.error.excludedNumber", [implode(",", $excludes), $data]));
        }

        $this->result = (float)$data;
    }

    public function serializeExtraData(): array {
        return [
            "type" => "number",
            "required" => $this->isRequired(),
            "min" => $this->getMin(),
            "max" => $this->getMax(),
            "excludes" => $this->getExcludes(),
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["mineflow"])) return null;

        if (isset($data["mineflow"]["placeholder"])) {
            return NumberInputPlaceholder::fromSerializedArray($data);
        }

        $required = $data["mineflow"]["required"] ?? false;
        $min = $data["mineflow"]["min"] ?? null;
        $max = $data["mineflow"]["max"] ?? null;
        $excludes = $data["mineflow"]["excludes"] ?? [];
        return new NumberInput($data["text"], $data["placeholder"], $data["default"] ?? "", $required, $min, $max, $excludes);
    }
}