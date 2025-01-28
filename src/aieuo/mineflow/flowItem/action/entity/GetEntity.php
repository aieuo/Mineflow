<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class GetEntity extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(int $entityId = null, string $resultName = "entity") {
        parent::__construct(self::GET_ENTITY, FlowItemCategory::ENTITY);

        $this->setArguments([
            NumberArgument::create("id", $entityId ?? "", "@action.getEntity.form.target")->min(0)->example("1"),
            StringArgument::create("result", $resultName)->example("entity"),
        ]);
    }

    public function getEntityId(): NumberArgument {
        return $this->getArgument("id");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("id");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $id = $this->getEntityId()->getInt($source);
        $resultName = $this->getResultName()->getString($source);

        $entity = EntityHolder::findEntity($id);
        if ($entity === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getEntity.notFound", [(string)$id]));
        }
        $source->addVariable($resultName, EntityVariable::fromObject($entity));

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(PlayerVariable::class, (string)$this->getEntityId())
        ];
    }
}