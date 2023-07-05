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

    public function __construct(string $position = "", string $resultName = "block") {
        parent::__construct(self::GET_BLOCK, FlowItemCategory::WORLD);

        $this->setArguments([
            new PositionArgument("position", $position),
            new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "block"),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->getArguments()[0];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->getPosition()->getPosition($source);
        $result = $this->getResultName()->getString($source);

        /** @var Position $position */
        $block = $position->world->getBlock($position);

        $variable = new BlockVariable($block);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(BlockVariable::class)
        ];
    }
}
