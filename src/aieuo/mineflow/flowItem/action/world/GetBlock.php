<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class GetBlock extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private PositionArgument $position;
    private StringArgument $resultName;

    public function __construct(string $position = "", string $resultName = "block") {
        parent::__construct(self::GET_BLOCK, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->position = new PositionArgument("position", $position),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "block"),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->position->getPosition($source);
        $result = $this->resultName->getString($source);

        /** @var Position $position */
        $block = $position->world->getBlock($position);

        $variable = new BlockVariable($block);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(BlockVariable::class)
        ];
    }
}
