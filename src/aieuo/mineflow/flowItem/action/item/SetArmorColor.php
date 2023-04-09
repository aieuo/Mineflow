<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\color\Color;
use pocketmine\item\Armor;

class SetArmorColor extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected string $id = self::SET_ARMOR_COLOR;

    protected string $name = "action.setArmorColor.name";
    protected string $detail = "action.setArmorColor.detail";
    protected array $detailDefaultReplace = ["armor", "red", "green", "blue"];

    protected string $category = FlowItemCategory::ITEM;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        string         $item = "",
        private string $red = "",
        private string $green = "",
        private string $blue = ""
    ) {
        $this->setItemVariableName($item);
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getItemVariableName(), $this->getRed(), $this->getGreen(), $this->getBlue()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $r = $source->replaceVariables($this->getRed());
        $g = $source->replaceVariables($this->getGreen());
        $b = $source->replaceVariables($this->getBlue());
        $this->throwIfInvalidNumber($r, 0, 255);
        $this->throwIfInvalidNumber($g, 0, 255);
        $this->throwIfInvalidNumber($b, 0, 255);

        $item = $this->getItem($source);
        if (!($item instanceof Armor)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.setArmorColor.not.armor", [$this->getItemVariableName()]));
        }

        $item->setCustomColor(new Color((int)$r, (int)$g, (int)$b));
        yield true;
        return $this->getItemVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleInput("@action.setArmorColor.form.red", "0", $this->getRed(), true),
            new ExampleInput("@action.setArmorColor.form.green", "255", $this->getGreen(), true),
            new ExampleInput("@action.setArmorColor.form.blue", "0", $this->getBlue(), true),
        ];
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
