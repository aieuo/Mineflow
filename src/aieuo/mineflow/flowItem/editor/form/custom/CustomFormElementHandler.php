<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\editor\form\custom;

class CustomFormElementHandler {
    public function __construct(
        private readonly int      $startIndex,
        private readonly int      $count,
        private readonly \Closure $handler,
    ) {
    }

    public function getStartIndex(): int {
        return $this->startIndex;
    }

    public function getElementCount(): int {
        return $this->count;
    }

    public function getHandler(): \Closure {
        return $this->handler;
    }
}