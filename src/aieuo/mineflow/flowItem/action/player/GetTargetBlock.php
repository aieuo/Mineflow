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
use SOFe\AwaitGenerator\Await;

class GetTargetBlock extends SimpleAction {

    public function __construct(string $player = "", int $max = 100, string $resultName = "block") {
        parent::__construct(self::GET_TARGET_BLOCK, FlowItemCategory::PLAYER);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new NumberArgument("maxDistance", $max, example: "100", min: 1),
            new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "block"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getMax(): NumberArgument {
        return $this->getArguments()[1];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $max = $this->getMax()->getInt($source);
        $result = $this->getResultName()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $block = $player->getTargetBlock($max);
        $source->addVariable($result, new BlockVariable($block));

        yield Await::ALL;
        return $this->getResultName()->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName()->get() => new DummyVariable(BlockVariable::class)
        ];
    }
}
