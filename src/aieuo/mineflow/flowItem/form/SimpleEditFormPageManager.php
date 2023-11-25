<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form;

use aieuo\mineflow\flowItem\form\page\EditPageBuilder;
use function array_values;

class SimpleEditFormPageManager {

    /** @var EditPageBuilder[] */
    private array $pages = [];

    public function set(int $number, EditPageBuilder $page): void {
        $this->pages[$number] = $page;
    }

    public function add(EditPageBuilder $page): void {
        $this->pages[] = $page;
    }

    public function get(int $number): ?EditPageBuilder {
        return $this->pages[$number] ?? null;
    }

    public function remove(EditPageBuilder $page): void {
        foreach ($this->pages as $i => $p) {
            if ($p === $page) {
                unset($this->pages[$i]);
                return;
            }
        }
    }

    public function all(): array {
        return array_values($this->pages);
    }

    public function count(): int {
        return count($this->pages);
    }
}
