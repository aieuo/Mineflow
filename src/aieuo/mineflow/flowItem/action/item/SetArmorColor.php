<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\color\Color;
use pocketmine\item\Armor;
use SOFe\AwaitGenerator\Await;

class SetArmorColor extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        string         $item = "",
        private string $red = "",
        private string $green = "",
        private string $blue = ""
    ) {
        parent::__construct(self::SET_ARMOR_COLOR, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["armor", "red", "green", "blue"];
    }

    public function getDetailReplaces(): array {
        return [$this->getItemVariableName(), $this->getRed(), $this->getGreen(), $this->getBlue()];
    }

    public function setRed(string $red): void {
        $this->red = $red;
    }

    public function getRed(): string {
        return $this->red;
    }

    public function setGreen(string $green): void {
        $this->green = $green;
    }

    public function getGreen(): string {
        return $this->green;
    }

    public function setBlue(string $blue): void {
        $this->blue = $blue;
    }

    public function getBlue(): string {
        return $this->blue;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "" and $this->red !== "" and $this->green !== "" and $this->blue !== "";
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $r = $this->getInt($source->replaceVariables($this->getRed()), 0, 255);
        $g = $this->getInt($source->replaceVariables($this->getGreen()), 0, 255);
        $b = $this->getInt($source->replaceVariables($this->getBlue()), 0, 255);

        $item = $this->getItem($source);
        if (!($item instanceof Armor)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.setArmorColor.not.armor", [$this->getItemVariableName()]));
        }

        $item->setCustomColor(new Color($r, $g, $b));
        yield Await::ALL;
        return $this->getItemVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleInput("@action.setArmorColor.form.red", "0", $this->getRed(), true),
            new ExampleInput("@action.setArmorColor.form.green", "255", $this->getGreen(), true),
            new ExampleInput("@action.setArmorColor.form.blue", "0", $this->getBlue(), true),
        ]);
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setRed($content[1]);
        $this->setGreen($content[2]);
        $this->setBlue($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getRed(), $this->getGreen(), $this->getBlue()];
    }
}
