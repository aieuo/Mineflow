<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form;

use function array_values;

class SimpleEditFormPageManager {

    /** @var SimpleEditFormBuilder[] */
    private array $pages = [];

    public function add(int $number, SimpleEditFormBuilder $page): void {
        $this->pages[] = $page;
    }

    public function get(int $number): ?SimpleEditFormBuilder {
        return $this->pages[$number] ?? null;
    }

    public function all(): array {
        return array_values($this->pages);
    }

    public function count(): int {
        return count($this->pages);
    }
}
