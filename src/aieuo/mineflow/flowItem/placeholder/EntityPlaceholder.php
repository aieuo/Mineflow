<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\placeholder;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EntityVariable;
use pocketmine\entity\Entity;
use pocketmine\player\Player;

class EntityPlaceholder extends Placeholder {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.entity", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getHuman(FlowItemExecutor $executor): Entity {
        $entity = $executor->replaceVariables($this->get());

        $variable = $executor->getVariable($entity);
        if ($variable instanceof EntityVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.entity"], $this->get()]));
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getOnlineEntity(FlowItemExecutor $executor): Entity {
        $entity = $this->getHuman($executor);
        if ($entity instanceof Player and !$entity->isOnline()) {
            throw new InvalidPlaceholderValueException(Language::get("action.error.entity.offline"));
        }
        return $entity;
    }

    public function createFormElement(array $variables): Element {
        return new EntityVariableDropdown($variables, $this->get(), $this->getDescription(), $this->isOptional());
    }
}