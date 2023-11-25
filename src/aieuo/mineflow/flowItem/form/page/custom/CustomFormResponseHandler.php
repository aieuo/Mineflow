<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form\page\custom;

use aieuo\mineflow\formAPI\element\Element;
use function array_map;
use function spl_object_id;

class CustomFormResponseHandler {

    /** @var int[] */
    private readonly array $elementIds;

    /**
     * @param array $elements
     * @param \Closure(mixed ...$values): void $handler
     */
    public function __construct(
        private readonly array    $elements,
        private readonly \Closure $handler,
    ) {
        $this->elementIds = array_map(fn(Element $e) => spl_object_id($e), $this->elements);
    }

    public function getElements(): array {
        return $this->elements;
    }

    public function getElementIds(): array {
        return $this->elementIds;
    }

    public function getHandler(): \Closure {
        return $this->handler;
    }
}
