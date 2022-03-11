<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\alias;

use aieuo\mineflow\flowItem\action\math\FourArithmeticOperations;

class FourArithmeticOperationsAdd extends FourArithmeticOperations implements FlowItemAlias {

    public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
        parent::__construct($value1, self::ADDITION, $value2, $resultName);
    }

    public function getId(): string {
        return self::FOUR_ARITHMETIC_OPERATIONS_ADD;
    }
}