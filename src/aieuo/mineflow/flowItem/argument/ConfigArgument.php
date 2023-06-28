<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ConfigVariable;
use pocketmine\utils\Config;

class ConfigArgument extends FlowItemArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.config", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getConfig(FlowItemExecutor $executor): Config {
        $config = $executor->replaceVariables($this->get());
        $variable = $executor->getVariable($config);

        if ($variable instanceof ConfigVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.config"], $this->get()]));
    }

    public function createFormElement(array $variables): Element {
        return new ConfigVariableDropdown($variables, $this->get(), $this->getDescription(), $this->isOptional());
    }
}