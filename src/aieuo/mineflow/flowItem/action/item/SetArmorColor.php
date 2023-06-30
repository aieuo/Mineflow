<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\color\Color;
use pocketmine\item\Armor;
use SOFe\AwaitGenerator\Await;

class SetArmorColor extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $armor;
    private NumberArgument $red;
    private NumberArgument $green;
    private NumberArgument $blue;

    public function __construct(string $item = "", string $red = "", string $green = "", string $blue = "") {
        parent::__construct(self::SET_ARMOR_COLOR, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->armor = new ItemArgument("armor", $item),
            $this->red = new NumberArgument("red", $red, example: "0", min: 0, max: 255),
            $this->green = new NumberArgument("green", $green, example: "255", min: 0, max: 255),
            $this->blue = new NumberArgument("blue", $blue, example: "0", min: 0, max: 255),
        ]);
    }

    public function getArmor(): ItemArgument {
        return $this->armor;
    }

    public function getRed(): NumberArgument {
        return $this->red;
    }

    public function getGreen(): NumberArgument {
        return $this->green;
    }

    public function getBlue(): NumberArgument {
        return $this->blue;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $r = $this->red->getInt($source);
        $g = $this->green->getInt($source);
        $b = $this->blue->getInt($source);

        $item = $this->armor->getItem($source);
        if (!($item instanceof Armor)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.setArmorColor.not.armor", [$this->armor->get()]));
        }

        $item->setCustomColor(new Color($r, $g, $b));
        yield Await::ALL;
        return $this->armor->get();
    }
}
