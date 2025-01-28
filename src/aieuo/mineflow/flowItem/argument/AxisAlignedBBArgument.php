<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\AxisAlignedBBVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use pocketmine\math\AxisAlignedBB;

class AxisAlignedBBArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.aabb", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getAxisAlignedBB(FlowItemExecutor $executor): AxisAlignedBB {
        $aabb = $this->getVariableName()->eval($executor->getVariableRegistryCopy());
        $variable = $executor->getVariable($aabb);

        if ($variable instanceof AxisAlignedBBVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.aabb"], (string)$this->getVariableName()]));
    }

    public function createFormElements(array $variables): array {
        return [
            new AxisAlignedBBVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}