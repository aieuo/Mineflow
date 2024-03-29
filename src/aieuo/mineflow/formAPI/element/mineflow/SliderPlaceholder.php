<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Slider;

class SliderPlaceholder extends Slider implements ElementPlaceholder {

    private string $minStr;
    private string $maxStr;
    private string $stepStr;
    private string $defaultStr;

    public function __construct(string $text, string $min, string $max, string $step = "1", string $default = null) {
        parent::__construct($text, (float)$min, (float)$max, (float)$step);
        $this->minStr = $min;
        $this->maxStr = $max;
        $this->stepStr = $step;
        $this->defaultStr = $default ?? $min;
    }

    public function getMinStr(): string {
        return $this->minStr;
    }

    public function setMinStr(string $minStr): void {
        $this->minStr = $minStr;
        $this->setMin((float)$minStr);
    }

    public function getMaxStr(): string {
        return $this->maxStr;
    }

    public function setMaxStr(string $maxStr): void {
        $this->maxStr = $maxStr;
        $this->setMax((float)$maxStr);
    }

    public function getStepStr(): string {
        return $this->stepStr;
    }

    public function setStepStr(string $stepStr): void {
        $this->stepStr = $stepStr;
        $this->setStep((float)$stepStr);
    }

    public function getDefaultStr(): string {
        return $this->defaultStr;
    }

    public function setDefaultStr(string $defaultStr): void {
        $this->defaultStr = $defaultStr;
        $this->setDefault((float)$defaultStr);
    }

    public function forceConvert(): Slider {
        return new Slider($this->getText(), $this->getMin(), $this->getMax(), $this->getStep(), $this->getDefault());
    }

    public function jsonSerialize(): array {
        $data = parent::jsonSerialize();
        $data["mineflow"]["placeholder"] = [
            "min" => $this->minStr,
            "max" => $this->maxStr,
            "step" => $this->stepStr,
            "default" => $this->defaultStr ?? $this->minStr,
        ];
        return $data;
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["mineflow"]["placeholder"]["min"]) or !isset($data["mineflow"]["placeholder"]["max"])) return null;

        $placeholder = $data["mineflow"]["placeholder"];
        return new SliderPlaceholder($data["text"], $placeholder["min"], $placeholder["max"], $placeholder["step"] ?? 1, $placeholder["default"] ?? null);
    }
}