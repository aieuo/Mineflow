<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use SOFe\AwaitGenerator\Await;

class GetTargetBlock extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private NumberArgument $max;
    private StringArgument $resultName;

    public function __construct(string $player = "", int $max = 100, string $resultName = "block") {
        parent::__construct(self::GET_TARGET_BLOCK, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
        $this->max = new NumberArgument("maxDistance", $max, example: "100", min: 1);
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "block");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "maxDistance", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->max->get(), $this->resultName->get()];
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->max->get() !== "" and $this->resultName->isNotEmpty();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getMax(): NumberArgument {
        return $this->max;
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

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->max->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->max->set($content[1]);
        $this->resultName->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->max->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(BlockVariable::class)
        ];
    }
}
