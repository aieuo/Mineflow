<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class GetTargetBlock extends SimpleAction {

    public function __construct(string $player = "", int $max = 100, string $resultName = "block") {
        parent::__construct(self::GET_TARGET_BLOCK, FlowItemCategory::PLAYER);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            NumberArgument::create("maxDistance", $max)->min(1)->example("100"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("block"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getMax(): NumberArgument {
        return $this->getArgument("maxDistance");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $max = $this->getMax()->getInt($source);
        $result = $this->getResultName()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $block = $player->getTargetBlock($max);
        $source->addVariable($result, new BlockVariable($block));

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(BlockVariable::class)
        ];
    }
}