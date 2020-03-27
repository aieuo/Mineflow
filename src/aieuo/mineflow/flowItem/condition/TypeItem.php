<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

abstract class TypeItem extends Condition implements PlayerFlowItem, ItemFlowItem {
    use PlayerFlowItemTrait, ItemFlowItemTrait;

    protected $detailDefaultReplace = ["player", "item"];

    protected $category = Categories::CATEGORY_CONDITION_ITEM;

    public function __construct(string $name = "target", string $item = "item") {
        $this->playerVariableName = $name;
        $this->itemVariableName = $item;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getItemVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getItemVariableName() !== "";
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@flowItem.form.target.item", Language::get("form.example", ["item"]), $default[2] ?? $this->getItemVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") $data[2] = "item";
        return ["status" => true, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setItemVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getItemVariableName()];
    }
}
