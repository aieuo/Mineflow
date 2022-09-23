<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

abstract class ObjectVariable extends Variable {

    private ?string $showString;

    public function __construct(?string $str = null) {
        $this->showString = $str;
    }

    public function getShowString(): ?string {
        return $this->showString;
    }

    public function __toString(): string {
        if (!empty($this->showString)) return (string)$this->showString;
        if (method_exists($this->getValue(), "__toString")) {
            $str = (string)$this->getValue();
        } else {
            $str = get_class($this->getValue());
        }
        return $str;
    }

    /**
     * @return array<string, DummyVariable>
     */
    public static function getValuesDummy(): array {
        return [];
    }
}
