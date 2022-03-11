<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\item\ItemFactory;

class SetItemDamage extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected string $name = "action.setItemDamage.name";
    protected string $detail = "action.setItemDamage.detail";
    protected array $detailDefaultReplace = ["item", "damage"];

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private string $damage;

    public function __construct(string $item = "", string $damage = "") {
        parent::__construct(self::SET_ITEM_DAMAGE, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
        $this->damage = $damage;
    }

    public function setDamage(string $damage): void {
        $this->damage = $damage;
    }

    public function getDamage(): string {
        return $this->damage;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "" and $this->damage !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getItemVariableName(), $this->getDamage()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $damage = $source->replaceVariables($this->getDamage());
        $this->throwIfInvalidNumber($damage, 0);

        $item = $this->getItem($source);

        $newItem = ItemFactory::getInstance()->get($item->getId(), (int)$damage, $item->getCount(), $item->getNamedTag());
        $this->getItemVariable($source)->setItem($newItem);

        yield true;
        return $this->getItemVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleNumberInput("@action.setDamage.form.damage", "0", $this->getDamage(), true, 0),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setDamage($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getDamage()];
    }
}
