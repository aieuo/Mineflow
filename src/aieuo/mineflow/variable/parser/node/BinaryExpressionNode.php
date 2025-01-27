<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class BinaryExpressionNode implements Node {

    public function __construct(
        private readonly Node   $left,
        private readonly string $operator,
        private readonly Node   $right,
    ) {
    }

    public function getLeft(): Node {
        return $this->left;
    }

    public function getOperator(): string {
        return $this->operator;
    }

    public function getRight(): Node {
        return $this->right;
    }

    public function __toString(): string {
        return $this->left.$this->operator.$this->right;
    }
}