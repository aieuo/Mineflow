<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class WrappedNode implements Node {

    public function __construct(
        private readonly Node $statement,
    ) {
    }

    public function getStatement(): Node {
        return $this->statement;
    }

    public function __toString(): string {
        return "(".$this->statement.")";
    }
}