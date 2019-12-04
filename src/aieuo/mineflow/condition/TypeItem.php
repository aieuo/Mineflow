<?php

namespace aieuo\mineflow\condition;

use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;

abstract class TypeItem extends Condition {

    protected $category = Categories::CATEGORY_CONDITION_ITEM;

    /** @var string */
    private $itemId;
    /** @var string */
    private $itemCount;
    /** @var string|null */
    private $itemName;

    public function __construct(Item $item = null) {
        if ($item === null) return;
        $this->itemId = $item->getId().":".$item->getDamage();
        $this->itemCount = (string)$item->getCount();
        $this->itemName = $item->hasCustomName() ? $item->getName() : null;
    }

    public function setItem(string $id, string $count, string $name = null): self {
        $this->itemId = $id;
        $this->itemCount = $count;
        $this->itemName = $name;
        return $this;
    }

    public function getItem(): array {
        return [$this->itemId, $this->itemCount, $this->itemName];
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        $item = $this->getItem();
        return Language::get($this->detail, $item);
    }

    public function isDataValid(): bool {
        return $this->itemId !== null and $this->itemCount !== null;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        $item = $this->getItem();
        $id = $item[0] ?? "";
        $count = $item[1] ?? "";
        $name = $item[2] ?? "";
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@condition.item.form.id", Language::get("form.example", ["1:0"]), $default[1] ?? $id),
                new Input("@condition.item.form.count", Language::get("form.example", ["16"]), $default[2] ?? $count),
                new Input("@condition.item.form.name", Language::get("form.example", ["aieuo"]), $default[3] ?? $name),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseItem(string $id, int $count, string $name = ""): ?Item {
        try {
            $item = ItemFactory::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
        $item->setCount($count);
        if (!empty($name)) $item->setCustomName($name);
        return $item;
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        $helper = Main::getInstance()->getVariableHelper();
        if (!$helper->containsVariable($data[1])) {
            try {
                ItemFactory::fromString($data[1]);
            } catch (\InvalidArgumentException $e) {
                $errors[] = ["@condition.item.notFound", 1];
            }
        }
        $containsVariable = $helper->containsVariable($data[2]);
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        } elseif (!$containsVariable and !is_numeric($data[2])) {
            $errors[] = ["@mineflow.contents.notNumber", 2];
        } elseif (!$containsVariable and (int)$data[2] <= 0) {
            $errors[] = ["@condition.item.form.zero", 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Condition {
        if (!isset($content[1])) return null;
        $this->setItem($content[0], $content[1], $content[2] ?? "");
        return $this;
    }

    public function serializeContents(): array {
        return $this->getItem();
    }
}