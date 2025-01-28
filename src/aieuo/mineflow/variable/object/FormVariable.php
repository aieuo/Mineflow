<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\ObjectVariable;

abstract class FormVariable extends ObjectVariable {

    abstract public function getValue(): Form;

    public function __toString(): string {
        return (string)Mineflow::getVariableHelper()->arrayToListVariable($this->getValue()->jsonSerialize());
    }
}