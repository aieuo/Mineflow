<?php

namespace aieuo\mineflow\formAPI\element;

use aieuo\mineflow\formAPI\element\mineflow\ElementPlaceholder;

class NumberInputPlaceholder extends NumberInput implements ElementPlaceholder {

    private ?string $minStr;
    private ?string $maxStr;

    public function __construct(string $text, string $placeholder = "", string $default = "", bool $required = false, ?string $min = null, ?string $max = null, array $excludes = []) {
        parent::__construct($text, $placeholder, $default, $required, $min === null ? null : (float)$min, $max === null ? null : (float)$max, $excludes);

        $this->minStr = $min;
        $this->maxStr = $max;
    }

    public function getMinStr(): ?string {
        return $this->minStr;
    }

    public function getMaxStr(): ?string {
        return $this->maxStr;
    }

    public function forceConvert(): NumberInput {
        return new NumberInput($this->getText(), $this->getPlaceholder(), $this->getDefault(), $this->isRequired(), $this->getMin(), $this->getMax(), $this->getExcludes());
    }

    public function serializeExtraData(): array {
        $data = parent::serializeExtraData();
        $data["placeholder"] = [
            "min" => $this->minStr,
            "max" => $this->maxStr,
        ];
        return $data;
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["mineflow"]["placeholder"])) return null;

        $required = $data["mineflow"]["required"] ?? false;
        $min = $data["mineflow"]["placeholder"]["min"] ?? null;
        $max = $data["mineflow"]["placeholder"]["max"] ?? null;
        $excludes = $data["mineflow"]["excludes"] ?? [];
        return new NumberInputPlaceholder($data["text"], $data["placeholder"], $data["default"] ?? "", $required, $min, $max, $excludes);
    }
}