<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EntityVariable;
use pocketmine\entity\Entity;
use pocketmine\player\Player;

class EntityArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.entity", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getEntity(FlowItemExecutor $executor): Entity {
        $entity = $this->getVariableName()->eval($executor->getVariableRegistryCopy());

        $variable = $executor->getVariable($entity);
        if ($variable instanceof EntityVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.entity"], (string)$this->getVariableName()]));
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getOnlineEntity(FlowItemExecutor $executor): Entity {
        $entity = $this->getEntity($executor);
        if ($entity instanceof Player and !$entity->isOnline()) {
            throw new InvalidPlaceholderValueException(Language::get("action.error.entity.offline"));
        }
        return $entity;
    }

    public function createFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}