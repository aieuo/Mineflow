<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\WorldVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\WorldVariable;
use pocketmine\world\World;

class WorldArgument extends FlowItemArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.world", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getWorld(FlowItemExecutor $executor): World {
        $world = $executor->replaceVariables($this->get());
        $variable = $executor->getVariable($world);

        if ($variable instanceof WorldVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.world"], $this->get()]));
    }

    public function createFormElement(array $variables): Element {
        return new WorldVariableDropdown($variables, $this->get(), $this->getDescription(), $this->isOptional());
    }
}