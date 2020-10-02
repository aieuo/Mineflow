<?php

namespace aieuo\mineflow\formAPI\element;

class NumberInput extends Input {

    /* @var float|null */
    private $min;
    /* @var float|null */
    private $max;
    /** @var array */
    private $excludes;

    public function __construct(string $text, string $placeholder = "", string $default = "", bool $required = false, ?float $min = null, ?float $max = null, array $excludes = []) {
        parent::__construct($text, $placeholder, $default, $required);

        $this->min = $min;
        $this->max = $max;
        $this->excludes = $excludes;
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
}