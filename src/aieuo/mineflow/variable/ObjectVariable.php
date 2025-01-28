<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

abstract class ObjectVariable extends Variable {

    public function __toString(): string {
        if (method_exists($this->getValue(), "__toString")) {
            $str = (string)$this->getValue();
        } else {
            $str = get_class($this->getValue());
        }
        return $str;
    }
}