<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\utils\FormUtils;

class DropdownOptionArgument extends SeparatedInputStringArrayArgument {

    /**
     * @param FlowItemExecutor $executor
     * @return string[]
     */
    public function getArray(FlowItemExecutor $executor): array {
        $values = [];
        foreach ($this->value as $string) {
            foreach (FormUtils::expandText($string, $executor->getVariableRegistryCopy()) as $value) {
                $values[] = $value;
            }
        }
        return $values;
    }
}