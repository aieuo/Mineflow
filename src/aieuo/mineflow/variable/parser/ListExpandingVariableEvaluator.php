<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;

use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\parser\node\ConcatenateNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\ToStringNode;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use function array_values;
use function count;
use function implode;
use function is_string;
use function iterator_to_array;

class ListExpandingVariableEvaluator extends VariableEvaluator {

    public function eval(Node $node): Variable {
        if ($node instanceof ToStringNode) {
            $variable = $this->eval($node->getNode());

            if ($variable instanceof IteratorVariable) {
                return $variable;
            }
            return new StringVariable((string)$node);
        }

        if ($node instanceof ConcatenateNode) {
            $values = [];
            $maxCount = 1;
            foreach ($node->getNodes() as $child) {
                $variable = $this->eval($child);
                if ($variable instanceof IteratorVariable) {
                    $value = array_values(iterator_to_array($variable->getIterator()));
                    $values[] = $value;

                    if (count($value) > $maxCount) {
                        $maxCount = count($value);
                    }
                } else {
                    $values[] = (string)$variable;
                }
            }

            $list = [];
            for ($i = 0; $i < $maxCount; $i ++) {
                $strings = [];
                foreach ($values as $value) {
                    if (is_string($value)) {
                        $strings[] = $value;
                    } else {
                        $strings[] = $value[$i] ?? "";
                    }
                }
                $list[] = new StringVariable(implode("", $strings));
            }

            return count($list) === 1 ? $list[0] : new ListVariable($list);
        }

        return parent::eval($node);
    }
}