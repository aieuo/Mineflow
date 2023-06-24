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
use SOFe\AwaitGenerator\Await;

class GetEntity extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private NumberArgument $entityId;
    private StringArgument $resultName;

    public function __construct(int $entityId = null, string $resultName = "entity") {
        parent::__construct(self::GET_ENTITY, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entityId = new NumberArgument("id", $entityId ?? "", "@action.getEntity.form.target", example: "1", min: 0),
            $this->resultName = new StringArgument("result", $resultName, example: "entity"),
        ]);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $id = $this->entityId->getInt($source);
        $resultName = $this->resultName->getString($source);

        $entity = EntityHolder::findEntity($id);
        if ($entity === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getEntity.notFound", [(string)$id]));
        }
        $source->addVariable($resultName, EntityVariable::fromObject($entity));

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(PlayerVariable::class, $this->entityId->get())
        ];
    }
}
