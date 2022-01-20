<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\alias;

use aieuo\mineflow\flowItem\action\math\Calculate2;

class Calculate2Round extends Calculate2 implements FlowItemAlias {

    protected string $id = self::CALCULATE2_ROUND;

    public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
        parent::__construct($value1, $value2, (string)self::CALC_ROUND, $resultName);
    }
}