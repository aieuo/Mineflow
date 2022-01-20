<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\alias;

use aieuo\mineflow\flowItem\action\math\FourArithmeticOperations;

class FourArithmeticOperationsDiv extends FourArithmeticOperations implements FlowItemAlias {

    protected string $id = self::FOUR_ARITHMETIC_OPERATIONS_DIV;

    public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
        parent::__construct($value1, self::DIVISION, $value2, $resultName);
    }
}