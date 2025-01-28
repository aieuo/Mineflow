<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ListFormVariableDropdown;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ListFormVariable;

class ListFormArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.listForm", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getListForm(FlowItemExecutor $executor): ListForm {
        $form = $this->getVariableName()->eval($executor->getVariableRegistryCopy());
        $variable = $executor->getVariable($form);

        if ($variable instanceof ListFormVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.listForm"], (string)$this->getVariableName()]));
    }

    public function createFormElements(array $variables): array {
        return [
            new ListFormVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}