<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class UnaryExpressionNode implements Node {

    public function __construct(
        private readonly string $operator,
        private readonly Node   $right,
    ) {
    }

    public function getOperator(): string {
        return $this->operator;
    }

    public function getRight(): Node {
        return $this->right;
    }

    public function __toString(): string {
        return $this->operator.$this->right;
    }
}