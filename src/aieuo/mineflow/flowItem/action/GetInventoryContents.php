<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\item\Item;

class GetInventoryContents extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::GET_INVENTORY_CONTENTS;

    protected $name = "action.getInventory.name";
    protected $detail = "action.getInventory.detail";
    protected $detailDefaultReplace = ["player", "inventory"];

    protected $category = Category::PLAYER;

    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $resultName;

    public function __construct(string $player = "", string $resultName = "inventory") {
        $this->setPlayerVariableName($player);
        $this->resultName = $resultName;
    }

    public function setResultName(string $health): void {
        $this->resultName = $health;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->resultName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getResultName()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $resultName = $origin->replaceVariables($this->getResultName());

        $entity = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($entity);

        $variable = new ListVariable(array_map(function (Item $item) {
            return new ItemObjectVariable($item);
        }, $entity->getInventory()->getContents()), $resultName);

        $origin->addVariable($variable);
        yield true;
        return $this->getResultName();
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
                new ExampleInput("@action.form.resultVariableName", "inventory", $this->getResultName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getResultName(), DummyVariable::LIST)];
    }
}