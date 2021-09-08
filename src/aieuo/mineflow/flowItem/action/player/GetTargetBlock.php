<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockObjectVariable;

class GetTargetBlock extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $id = self::GET_TARGET_BLOCK;

    protected string $name = "action.getTargetBlock.name";
    protected string $detail = "action.getTargetBlock.detail";
    protected array $detailDefaultReplace = ["player", "maxDistance", "result"];

    protected string $category = Category::PLAYER;

    private string $max;

    private string $resultName;

    public function __construct(string $player = "", string $max = "100", string $result = "block") {
        $this->setPlayerVariableName($player);
        $this->max = $max;
        $this->resultName = $result;
    }

    public function setMax(string $max): void {
        $this->max = $max;
    }

    public function getMax(): string {
        return $this->max;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->max !== "" and $this->resultName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getMax(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $max = $source->replaceVariables($this->getMax());
        $this->throwIfInvalidNumber($max, 1);
        $result = $source->replaceVariables($this->getResultName());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $block = $player->getTargetBlock((int)$max);
        $source->addVariable($result, new BlockObjectVariable($block));
        yield true;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleNumberInput("@action.getTargetBlock.form.max", "100", $this->getMax(), true),
            new ExampleInput("@action.form.resultVariableName", "block", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setMax($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getMax(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(BlockObjectVariable::class)
        ];
    }
}