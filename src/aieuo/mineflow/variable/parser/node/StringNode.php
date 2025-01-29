<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\node;

class StringNode implements Node {

    public function __construct(
        private readonly string $string,
        private readonly string $trimmedLeft = "",
        private readonly string $trimmedRight = "",
    ) {
    }

    public function getString(): string {
        return $this->string;
    }

    public function getTrimmedLeft(): string {
        return $this->trimmedLeft;
    }

    public function getTrimmedRight(): string {
        return $this->trimmedRight;
    }

    public function __toString(): string {
        return $this->trimmedLeft.$this->string.$this->trimmedRight;
    }
}