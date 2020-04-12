<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class SetItem extends Action implements PlayerFlowItem, ItemFlowItem {
    use PlayerFlowItemTrait, ItemFlowItemTrait;

    protected $id = self::SET_ITEM;

    protected $name = "action.setItem.name";
    protected $detail = "action.setItem.detail";
    protected $detailDefaultReplace = ["player", "item", "index"];

    protected $category = Category::INVENTORY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $index;

    public function __construct(string $name = "target", string $item = "item", string $index = "") {
        $this->playerVariableName = $name;
        $this->itemVariableName = $item;
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $index = $origin->replaceVariables($this->getIndex());

        $this->throwIfInvalidNumber($index, 0);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player->getInventory()->setItem($index, $item);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@flowItem.form.target.item", Language::get("form.example", ["item"]), $default[2] ?? $this->getItemVariableName()),
                new Input("@action.setItem.form.index", Language::get("form.example", ["0"]), $default[3] ?? $this->getIndex()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") $data[2] = "item";
        $containsVariable = Main::getVariableHelper()->containsVariable($data[3]);
        if ($data[3] === "") {
            $errors[] = ["@form.insufficient", 3];
        } elseif (!$containsVariable and !is_numeric($data[3])) {
            $errors[] = ["@flowItem.error.notNumber", 3];
        } elseif (!$containsVariable and (float)$data[3] < 0) {
            $errors[] = [Language::get("flowItem.error.lessValue", [0]), 3];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setItemVariableName($content[1]);
        $this->setIndex($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getItemVariableName(), $this->getIndex()];
    }
}