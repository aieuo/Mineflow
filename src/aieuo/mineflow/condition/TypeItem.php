<?php

namespace aieuo\mineflow\condition;

use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\condition\Condition;
use aieuo\mineflow\FormAPI\element\Toggle;

abstract class TypeItem extends Condition {

    protected $category = Categories::CATEGORY_CONDITION_ITEM;

    /** @var Item */
    private $item;

    public function __construct(Item $item = null) {
        $this->item = $item;
    }

    public function setItem(Item $item): self {
        $this->item = $item;
        return $this;
    }

    public function getItem(): ?Item {
        return $this->item;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        $item = $this->getItem();
        return Language::get($this->detail, [$item->getId(), $item->getDamage(), $item->getName(), $item->getCount()]);
    }

    public function isDataValid(): bool {
        return $this->item instanceof Item and $this->item->getCount() > 0;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        $item = $this->getItem();
        $id = "";
        $count = "";
        $name = "";
        if ($item instanceof Item) {
            $id = $item->getId().":".$item->getDamage();
            $count = $item->getCount();
            $name = $item->hasCustomName() ? $item->getName() : "";
        }
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@condition.item.form.id", Language::get("form.example", ["1:0"]), $default[1] ?? $id),
                new Input("@condition.item.form.count", Language::get("form.example", ["16"]), $default[2] ?? $count),
                new Input("@condition.item.form.name", Language::get("form.example", ["aieuo"]), $default[3] ?? $name),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        try {
            ItemFactory::fromString($data[1]);
        } catch (\InvalidArgumentException $e) {
            $errors[] = ["@condition.item.notFound", 1];
        }
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        } elseif (!is_numeric($data[2])) {
            $errors[] = ["@condition.item.count.notNumber", 2];
        } elseif ((int)$data[2] <= 0) {
            $errors[] = ["@condition.item.form.zero", 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Condition {
        if (!isset($content[1])) return null;
        try {
            $item = ItemFactory::fromString($content[0]);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
        $item->setCount((int)$content[1]);
        if (!empty($content[2])) $item->setCustomName($content[2]);
        $this->setItem($item);
        return $this;
    }

    public function serializeContents(): array {
        $item = $this->getItem();
        return [
            $item->getId().":".$item->getDamage(),
            $item->getCount(),
            $item->hasCustomName() ? $item->getCustomName() : "",
        ];
    }
}