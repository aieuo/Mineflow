<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\placeholder;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\Vector3VariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\Vector3Variable;
use pocketmine\math\Vector3;

class Vector3Placeholder extends Placeholder {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.vector3", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getVector3(FlowItemExecutor $executor): Vector3 {
        $vector3 = $executor->replaceVariables($this->get());
        $variable = $executor->getVariable($vector3);

        if ($variable instanceof Vector3Variable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.vector3"], $this->get()]));
    }

    public function createFormElement(array $variables): Element {
        return new Vector3VariableDropdown($variables, $this->get(), $this->getDescription(), $this->isOptional());
    }
}