<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\alias;

use aieuo\mineflow\flowItem\action\math\FourArithmeticOperations;

class FourArithmeticOperationsMul extends FourArithmeticOperations implements FlowItemAlias {

    public function __construct(float $value1 = null, float $value2 = null, string $resultName = "result") {
        parent::__construct($value1, self::MULTIPLICATION, $value2, $resultName);
    }

    public function getId(): string {
        return self::FOUR_ARITHMETIC_OPERATIONS_MUL;
    }
}