<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

use function array_is_list;

trait IteratorVariableTrait {

    public function pluck(string $index): ?Variable {
        $newValues = [];
        foreach ($this->getIterator() as $i => $value) {
            $property = $value->getProperty($index);
            if ($property === null) return null;
            $newValues[$i] = $property;
        }
        return array_is_list($newValues) ? new ListVariable($newValues) : new MapVariable($newValues);
    }

}
