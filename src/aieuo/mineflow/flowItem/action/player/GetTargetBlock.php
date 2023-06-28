<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use SOFe\AwaitGenerator\Await;

class GetTargetBlock extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private NumberArgument $max;

    public function __construct(
        string         $player = "",
        int            $max = 100,
        private string $resultName = "block"
    ) {
        parent::__construct(self::GET_TARGET_BLOCK, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
        $this->max = new NumberArgument("max", $max, example: "100", min: 1);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "maxDistance", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->max->get(), $this->getResultName()];
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->max->get() !== "" and $this->resultName !== "";
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getMax(): NumberArgument {
        return $this->max;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $max = $this->max->getInt($source);
        $result = $source->replaceVariables($this->getResultName());
        $player = $this->player->getOnlinePlayer($source);

        $block = $player->getTargetBlock($max);
        $source->addVariable($result, new BlockVariable($block));

        yield Await::ALL;
        return $this->getResultName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->max->createFormElement($variables),
            new ExampleInput("@action.form.resultVariableName", "block", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->max->set($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->max->get(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(BlockVariable::class)
        ];
    }
}
