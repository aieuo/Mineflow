<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class ToStringNode implements Node {

    public function __construct(
        private readonly Node $node,
    ) {
    }

    public function getNode(): Node {
        return $this->node;
    }

    public function __toString(): string {
        return "{".$this->node."}";
    }
}