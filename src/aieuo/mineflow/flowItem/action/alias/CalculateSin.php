<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\alias;

use aieuo\mineflow\flowItem\action\math\Calculate;

class CalculateSin extends Calculate implements FlowItemAlias {

    protected string $id = self::CALCULATE_SIN;

    public function __construct(string $value = "", string $resultName = "result") {
        parent::__construct($value, (string)self::CALC_SIN, $resultName);
    }
}