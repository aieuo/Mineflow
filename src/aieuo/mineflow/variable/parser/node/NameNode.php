<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class NameNode implements Node {

    public function __construct(
        private readonly string $name,
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

}
