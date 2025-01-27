<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\FormVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\FormVariable;

class FormArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.form", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getForm(FlowItemExecutor $executor): Form {
        $form = $this->getVariableName()->eval($executor->getVariableRegistryCopy());
        $variable = $executor->getVariable($form);

        if ($variable instanceof FormVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.form"], (string)$this->getVariableName()]));
    }

    public function createFormElements(array $variables): array {
        return [
            new FormVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}