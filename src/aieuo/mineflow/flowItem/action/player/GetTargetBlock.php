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

    private PlayerArgument $player;
    private NumberArgument $max;
    private StringArgument $resultName;

    public function __construct(string $player = "", int $max = 100, string $resultName = "block") {
        parent::__construct(self::GET_TARGET_BLOCK, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->max = new NumberArgument("maxDistance", $max, example: "100", min: 1),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "block"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getMax(): NumberArgument {
        return $this->max;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $max = $this->max->getInt($source);
        $result = $this->resultName->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        $block = $player->getTargetBlock($max);
        $source->addVariable($result, new BlockVariable($block));

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(BlockVariable::class)
        ];
    }
}
