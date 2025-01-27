<?php
declare(strict_types=1);


namespace aieuo\mineflow\utils\dependency;

use function array_map;
use function array_pop;

class DependencyResult {
    public function __construct(
        private ?array $order,
        private array $remains,
    ) {
    }

    public function getOrder(): array {
        return $this->order ?? [];
    }

    public function getRemains(): array {
        return $this->remains;
    }

    public function hasCircularDependency(): bool {
        return $this->order === null;
    }

    public function getCircularPath(): array {
        $dependency = new DependencySolver();
        foreach ($this->remains as $from => $to) {
            $dependency->add($from, $to);
        }
        $result = $dependency->solve()->getRemains();

        $graph = [];
        foreach ($result as $from => $dependencies) {
            if (!isset($graph[$from])) {
                $graph[$from] = [];
            }
            foreach ($dependencies as $dependency) {
                $graph[$dependency][] = $from;
            }
        }

        $path = [];
        $nodes = array_keys($graph);
        $start = array_pop($nodes);
        $visited = [$start];
        $stack = array_map(fn($v) => [$v, [$start, $v]], $graph[$start]);
        while (!empty($stack)) {
            [$v, $path] = array_pop($stack);
            if ($v === $start) break;

            if (!in_array($v, $visited, true)) {
                $visited[] = $v;

                foreach ($graph[$v] as $neighbor) {
                    $stack[] = [$neighbor, array_merge($path, [$neighbor])];
                }
            }
        }

        return $path;
    }
}