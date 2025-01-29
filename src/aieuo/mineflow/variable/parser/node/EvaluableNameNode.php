<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class EvaluableNameNode implements Node {

    public function __construct(
        private readonly Node $name,
    ) {
    }

    public function getName(): Node {
        return $this->name;
    }

    public function __toString(): string {
        return (string)$this->name;
    }
}