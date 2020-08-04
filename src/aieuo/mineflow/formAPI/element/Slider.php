<?php

namespace aieuo\mineflow\formAPI\element;

class Slider extends Element {

    /** @var string */
    protected $type = self::ELEMENT_SLIDER;

    /** @var float */
    private $min;
    /** @var float */
    private $max;
    /** @var float */
    private $step;
    /** @var float */
    private $default;

    public function __construct(string $text, float $min, float $max, float $step = 1.0, ?int $default = null) {
        parent::__construct($text);
        $this->min = $min;
        $this->max = $max;
        $this->step = $step;
        $this->default = $default ?? $min;
    }

    /**
     * @param float $min
     * @return self
     */
    public function setMin(float $min): self {
        $this->min = $min;
        return $this;
    }

    /**
     * @return float
     */
    public function getMin(): float {
        return $this->min;
    }

    /**
     * @param float $max
     * @return self
     */
    public function setMax(float $max): self {
        $this->max = $max;
        return $this;
    }

    /**
     * @return float
     */
    public function getMax(): float {
        return $this->max;
    }

    /**
     * @param float $step
     * @return self
     */
    public function setStep(float $step): self {
        $this->step = $step;
        return $this;
    }

    /**
     * @return float
     */
    public function getStep(): float {
        return $this->step;
    }

    /**
     * @param float $default
     * @return self
     */
    public function setDefault(float $default): self {
        $this->default = $default;
        return $this;
    }

    /**
     * @return float
     */
    public function getDefault(): float {
        return $this->default;
    }

    public function jsonSerialize(): array {
        if ($this->min > $this->max) {
            list($this->min, $this->max) = [$this->max, $this->min]; // 入れ替える
        }
        if ($this->default === null or $this->default < $this->min) {
            $this->default = $this->min;
        }
        return [
            "type" => $this->type,
            "text" => str_replace("\\n", "\n", $this->reflectHighlight($this->checkTranslate($this->text))),
            "min" => $this->min,
            "max" => $this->max,
            "step" => $this->step,
            "default" => $this->default,
        ];
    }
}