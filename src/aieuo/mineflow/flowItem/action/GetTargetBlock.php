<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\variable\object\BlockObjectVariable;

class GetTargetBlock extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::GET_TARGET_BLOCK;

    protected $name = "action.getTargetBlock.name";
    protected $detail = "action.getTargetBlock.detail";
    protected $detailDefaultReplace = ["player", "maxDistance", "result"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $max;

    /** @var string */
    private $resultName;

    public function __construct(string $player = "target", string $max = "100", string $result = "block") {
        $this->setPlayerVariableName($player);
        $this->max = $max;
        $this->resultName = $result;
    }

    public function setMax(string $max) {
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $max = $origin->replaceVariables($this->getMax());
        $this->throwIfInvalidNumber($max, 1);
        $result = $origin->replaceVariables($this->getResultName());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $block = $player->getTargetBlock($max);
        $origin->addVariable(new BlockObjectVariable($block, $result));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.player", "target", $default[1] ?? $this->getPlayerVariableName(), true),
                new ExampleNumberInput("@action.getTargetBlock.form.max", "100", $default[2] ?? $this->getMax(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "block", $default[3] ?? $this->getResultName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setMax($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getMax(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return $this->getResultName();
    }
}