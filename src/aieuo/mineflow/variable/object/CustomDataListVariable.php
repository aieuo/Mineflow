<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\CustomVariableData;
use aieuo\mineflow\variable\Variable;

class CustomDataListVariable extends Variable {

    public static function getTypeName(): string {
        return "custom_variable_data";
    }

    public function __construct(private string $variableType, private string $key) {
    }

    /**
     * @return array<CustomVariableData>
     */
    public function getValue(): array {
        return Mineflow::getVariableHelper()->getAllCustomVariableData($this->variableType);
    }

    protected function getValueFromIndex(string $index): ?Variable {
        $list = $this->getValue();
        return ($list[$index] ?? null)?->getData($this->key);
    }

    public function __toString(): string {
        return $this->variableType."#data";
    }
}