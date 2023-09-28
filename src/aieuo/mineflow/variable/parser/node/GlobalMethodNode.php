<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class GlobalMethodNode implements Node {

    /**
     * @param Node $identifier
     * @param Node[] $arguments
     */
    public function __construct(
        private readonly Node  $identifier,
        private readonly array $arguments = [],
    ) {
    }

    public function getIdentifier(): Node {
        return $this->identifier;
    }

    public function getArguments(): array {
        return $this->arguments;
    }

}
