<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\alias;

use aieuo\mineflow\flowItem\action\math\Calculate;

class CalculateCos extends Calculate implements FlowItemAlias {

    protected string $id = self::CALCULATE_COS;

    public function __construct(string $value = "", string $resultName = "result") {
        parent::__construct($value, (string)self::CALC_COS, $resultName);
    }
}