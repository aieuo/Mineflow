<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

interface IteratorVariable extends \IteratorAggregate {

    public function pluck(string $index): ?Variable;
}
