<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\ItemPlaceholder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;

abstract class TypeItem extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected PlayerPlaceholder $player;
    protected ItemPlaceholder $item;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::INVENTORY,
        string $player = "",
        string $item = ""
    ) {
        parent::__construct($id, $category);

        $this->player = new PlayerPlaceholder("player", $player);
        $this->item = new ItemPlaceholder("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), $this->item->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->item->get()];
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->item->isNotEmpty();
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    public function getItem(): ItemPlaceholder {
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
