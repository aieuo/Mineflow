<?php
declare(strict_types=1);


namespace aieuo\mineflow\utils\dependency;

use function array_merge;
use function count;

class DependencySolver {

    private array $dependencies = [];

    public function add(string $name, array $dependencies): void {
        if (isset($this->dependencies[$name])) {
            $this->dependencies[$name] = array_merge($this->dependencies[$name], $dependencies);
        } else {
            $this->dependencies[$name] = $dependencies;
        }
    }

    public function solve(): DependencyResult {
        $graph = [];
        $inDegree = [];
        foreach ($this->dependencies as $from => $dependencies) {
            if (!isset($graph[$from])) $graph[$from] = [];
            foreach ($dependencies as $dependency) {
                $graph[$dependency][] = $from;
                
                if (!isset($inDegree[$dependency])) $inDegree[$dependency] = 0;
            }
            $inDegree[$from] = count($dependencies);
        }
        $_graph = $graph;

        $queue = [];
        foreach ($inDegree as $node => $num) {
            if ($num === 0) {
                $queue[] = $node;
            }
        }

        $result = [];
        while (!empty($queue)) {
            $v = array_shift($queue);
            $result[] = $v;
            foreach ($graph[$v] as $to) {
                $inDegree[$to] --;
                if ($inDegree[$to] === 0) {
                    $queue[] = $to;
                }
            }
            unset($_graph[$v]);
        }

        if (count($result) !== count($graph)) {
            return new DependencyResult(null, $_graph);
        }

        return new DependencyResult($result, $_graph);
    }
}