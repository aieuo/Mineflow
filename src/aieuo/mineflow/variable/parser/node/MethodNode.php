<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class MethodNode implements Node {

    /**
     * @param Node $left
     * @param Node $identifier
     * @param Node[] $arguments
     */
    public function __construct(
        private readonly Node  $left,
        private readonly Node  $identifier,
        private readonly array $arguments = [],
    ) {
    }

    public function getLeft(): Node {
        return $this->left;
    }

    public function getIdentifier(): Node {
        return $this->identifier;
    }

    public function getArguments(): array {
        return $this->arguments;
    }

    public function __toString(): string {
        $args = array_map(fn(Node $node) => (string)$node, $this->arguments);
        return $this->left.".".$this->identifier."(".implode(",", $args).")";
    }
}