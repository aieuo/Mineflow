<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ConfigVariable;
use pocketmine\utils\Config;

class ConfigArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.config", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getConfig(FlowItemExecutor $executor): Config {
        $config = $this->getVariableName()->eval($executor->getVariableRegistryCopy());
        $variable = $executor->getVariable($config);

        if ($variable instanceof ConfigVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.config"], (string)$this->getVariableName()]));
    }

    public function createFormElements(array $variables): array {
        return [
            new ConfigVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}