<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class SetItem extends FlowItem implements PlayerFlowItem, ItemFlowItem {
    use PlayerFlowItemTrait, ItemFlowItemTrait;

    protected $id = self::SET_ITEM;

    protected $name = "action.setItem.name";
    protected $detail = "action.setItem.detail";
    protected $detailDefaultReplace = ["player", "item", "index"];

    protected $category = Category::INVENTORY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $index;

    public function __construct(string $player = "", string $item = "", string $index = "") {
        $this->setPlayerVariableName($player);
        $this->setItemVariableName($item);
        $this->index = $index;
    }

    public function setIndex(string $health) {
        $this->index = $health;
    }

    public function getIndex(): string {
        return $this->index;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getItemVariableName() !== "" and $this->index !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getItemVariableName(), $this->getIndex()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $index = $origin->replaceVariables($this->getIndex());

        $this->throwIfInvalidNumber($index, 0);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player->getInventory()->setItem($index, $item);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
                new ItemVariableDropdown($variables, $this->getItemVariableName()),
                new ExampleNumberInput("@action.setItem.form.index", "0", $this->getIndex(), true, 0),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setItemVariableName($content[1]);
        $this->setIndex($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getItemVariableName(), $this->getIndex()];
    }
}