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
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

abstract class GetNearestEntityBase extends SimpleAction {

    public function __construct(
        string $id,
        string $category = FlowItemCategory::WORLD,
        string $position = "",
        int    $maxDistance = 100,
        string $resultName = "entity"
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            PositionArgument::create("position", $position),
            NumberArgument::create("distance", $maxDistance, "@action.getNearestEntity.form.maxDistance")->example("100"),
            StringArgument::create("entity", $resultName, "@action.form.resultVariableName")->example("entity"),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->getArgument("position");
    }

    public function getMaxDistance(): NumberArgument {
        return $this->getArgument("distance");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("entity");
    }

    /**
     * @return class-string<Entity>
     */
    abstract public function getTargetClass(): string;

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->getPosition()->getPosition($source);
        $result = $this->getResultName()->getString($source);
        $maxDistance = $this->getMaxDistance()->getFloat($source);

        $entity = $position->world->getNearestEntity($position, $maxDistance, $this->getTargetClass());

        $variable = $entity === null ? new NullVariable() : EntityVariable::fromObject($entity);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(EntityVariable::class, "nullable")
        ];
    }
}