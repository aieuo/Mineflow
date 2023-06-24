<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\ItemPlaceholder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use SOFe\AwaitGenerator\Await;

class SetItem extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerPlaceholder $player;
    private ItemPlaceholder $item;

    public function __construct(string $player = "", string $item = "", private string $index = "") {
        parent::__construct(self::SET_ITEM, FlowItemCategory::INVENTORY);

        $this->player = new PlayerPlaceholder("player", $player);
        $this->item = new ItemPlaceholder("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), $this->item->getName(), "index"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->item->get(), $this->getIndex()];
    }

    public function getItem(): ItemPlaceholder {
        return $this->item;
    }

    public function setIndex(string $health): void {
        $this->index = $health;
    }

    public function getIndex(): string {
        return $this->index;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->item->isNotEmpty() and $this->index !== "";
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $index = $this->getInt($source->replaceVariables($this->getIndex()), 0);
        $player = $this->player->getOnlinePlayer($source);

        $item = $this->item->getItem($source);

        $player->getInventory()->setItem($index, $item);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->item->createFormElement($variables),
            new ExampleNumberInput("@action.setItem.form.index", "0", $this->getIndex(), true, 0),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->item->set($content[1]);
        $this->setIndex($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->item->get(), $this->getIndex()];
    }
}
