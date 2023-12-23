<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CustomFormVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\CustomFormVariable;

class CustomFormArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.customForm", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getCustomForm(FlowItemExecutor $executor): CustomForm {
        $form = $this->getVariableName()->eval($executor->getVariableRegistryCopy());
        $variable = $executor->getVariable($form);

        if ($variable instanceof CustomFormVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.customForm"], (string)$this->getVariableName()]));
    }

    public function createFormElements(array $variables): array {
        return [
            new CustomFormVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}
