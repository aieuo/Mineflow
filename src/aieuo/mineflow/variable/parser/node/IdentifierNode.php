<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class IdentifierNode implements Node {

    public function __construct(
        private readonly string $name,
        private readonly string $trimmedLeft = "",
        private readonly string $trimmedRight = "",
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getTrimmedLeft(): string {
        return $this->trimmedLeft;
    }

    public function getTrimmedRight(): string {
        return $this->trimmedRight;
    }

    public function __toString(): string {
        return $this->trimmedLeft.$this->name.$this->trimmedRight;
    }
}