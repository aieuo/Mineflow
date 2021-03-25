<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class SetItemDamage extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::SET_ITEM_DAMAGE;

    protected $name = "action.setItemDamage.name";
    protected $detail = "action.setItemDamage.detail";
    protected $detailDefaultReplace = ["item", "damage"];

    protected $category = Category::ITEM;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $damage;

    public function __construct(string $item = "", string $damage = "") {
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

        $item->setDamage((int)$damage);
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
