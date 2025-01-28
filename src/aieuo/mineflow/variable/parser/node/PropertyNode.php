<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class PropertyNode implements Node {

    public function __construct(
        private readonly Node $left,
        private readonly Node $identifier,
    ) {
    }

    public function getLeft(): Node {
        return $this->left;
    }

    public function getIdentifier(): Node {
        return $this->identifier;
    }

    public function __toString(): string {
        return $this->left.".".$this->identifier;
    }
}