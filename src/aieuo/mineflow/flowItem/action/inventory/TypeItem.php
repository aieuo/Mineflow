<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;

abstract class TypeItem extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected PlayerArgument $player;
    protected ItemArgument $item;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::INVENTORY,
        string $player = "",
        string $item = ""
    ) {
        parent::__construct($id, $category);

        $this->player = new PlayerArgument("player", $player);
        $this->item = new ItemArgument("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), $this->item->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->item->get()];
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->item->isValid();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->item->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->item->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->item->get()];
    }
}
