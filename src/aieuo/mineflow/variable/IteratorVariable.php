<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

interface IteratorVariable extends \IteratorAggregate {

    public function pluck(string $index): ?Variable;

    public function first(): ?Variable;

    public function last(): ?Variable;

    public function firstKey(): ?Variable;

    public function lastKey(): ?Variable;

    public function keys(): ListVariable;

    public function values(): ListVariable;

    public function count(): NumberVariable;
}
