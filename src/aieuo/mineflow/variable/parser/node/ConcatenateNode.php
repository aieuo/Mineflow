<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class ConcatenateNode implements Node {

    /**
     * @param Node[] $nodes
     */
    public function __construct(
        private readonly array $nodes,
    ) {
    }

    public function getNodes(): array {
        return $this->nodes;
    }

    public function __toString(): string {
        return implode("", array_map(fn(Node $node) => (string)$node, $this->nodes));
    }
}