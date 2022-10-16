<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use pocketmine\item\ItemFactory;
use SOFe\AwaitGenerator\Await;

class SetItemDamage extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", private string $damage = "") {
        parent::__construct(self::SET_ITEM_DAMAGE, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["item", "damage"];
    }

    public function getDetailReplaces(): array {
        return [$this->getItemVariableName(), $this->getDamage()];
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $damage = $source->replaceVariables($this->getDamage());
        $this->throwIfInvalidNumber($damage, 0);

        $item = $this->getItem($source);

        $newItem = ItemFactory::getInstance()->get($item->getId(), (int)$damage, $item->getCount(), $item->getNamedTag());
        $this->getItemVariable($source)->setItem($newItem);

        yield Await::ALL;
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
