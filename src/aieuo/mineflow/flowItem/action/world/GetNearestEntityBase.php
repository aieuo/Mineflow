<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NullVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use pocketmine\entity\Entity;
use SOFe\AwaitGenerator\Await;

abstract class GetNearestEntityBase extends SimpleAction {

    protected PositionArgument $position;
    protected NumberArgument $maxDistance;
    protected StringArgument $resultName;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::WORLD,
        string $position = "",
        int    $maxDistance = 100,
        string $resultName = "entity"
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->position = new PositionArgument("position", $position),
            $this->maxDistance = new NumberArgument("distance", $maxDistance, "@action.getNearestEntity.form.maxDistance", example: "100"),
            $this->resultName = new StringArgument("entity", $resultName, "@action.form.resultVariableName", example: "entity"),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    public function getMaxDistance(): NumberArgument {
        return $this->maxDistance;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    /**
     * @return class-string<Entity>
     */
    abstract public function getTargetClass(): string;

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->position->getPosition($source);
        $result = $this->resultName->getString($source);
        $maxDistance = $this->maxDistance->getFloat($source);

        $entity = $position->world->getNearestEntity($position, $maxDistance, $this->getTargetClass());

        $variable = $entity === null ? new NullVariable() : EntityVariable::fromObject($entity);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return $this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(EntityVariable::class, "nullable")
        ];
    }
}
