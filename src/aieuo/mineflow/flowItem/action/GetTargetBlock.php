<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockObjectVariable;

class GetTargetBlock extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::GET_TARGET_BLOCK;

    protected $name = "action.getTargetBlock.name";
    protected $detail = "action.getTargetBlock.detail";
    protected $detailDefaultReplace = ["player", "maxDistance", "result"];

    protected $category = Category::PLAYER;

    /** @var string */
    private $max;

    /** @var string */
    private $resultName;

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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $max = $origin->replaceVariables($this->getMax());
        $this->throwIfInvalidNumber($max, 1);
        $result = $origin->replaceVariables($this->getResultName());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $block = $player->getTargetBlock((int)$max);
        $origin->addVariable(new BlockObjectVariable($block, $result));
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
        return [new DummyVariable($this->getResultName(), DummyVariable::BLOCK)];
    }
}