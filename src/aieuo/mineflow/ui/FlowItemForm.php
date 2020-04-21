<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\action\Action;
use aieuo\mineflow\flowItem\action\ActionContainer;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\ConditionContainer;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class FlowItemForm {

    public function sendChangeName(Player $player, FlowItem $item, FlowItemContainer $container) {
        (new CustomForm(Language::get("form.recipe.changeName.title", [$item->getName()])))
            ->setContents([
                new Input("@form.recipe.changeName.content1", "", $item->getCustomName()),
                new Toggle("@form.cancelAndBack")
            ])->onReceive(function (Player $player, array $data, FlowItem $item, FlowItemContainer $container) {
                if ($data[1]) {
                    if ($container instanceof FlowItem and $container->hasCustomMenu()) {
                        $container->sendCustomMenu($player, ["@form.cancelled"]);
                    } elseif ($item instanceof Action and $container instanceof ActionContainer) {
                        (new ActionForm)->sendAddedActionMenu($player, $container, $item, ["@form.cancelled"]);
                    } elseif ($item instanceof Condition and $container instanceof ConditionContainer) {
                        (new ConditionForm)->sendAddedConditionMenu($player, $container, $item, ["@form.cancelled"]);
                    }
                    return;
                }

                $item->setCustomName($data[0]);
                if ($container instanceof FlowItem and $container->hasCustomMenu()) {
                    $container->sendCustomMenu($player, ["@form.changed"]);
                } elseif ($item instanceof Action and $container instanceof ActionContainer) {
                    (new ActionForm)->sendAddedActionMenu($player, $container, $item, ["@form.changed"]);
                } elseif ($item instanceof Condition and $container instanceof ConditionContainer) {
                    (new ConditionForm)->sendAddedConditionMenu($player, $container, $item, ["@form.changed"]);
                }
            })->addArgs($item, $container)->show($player);
    }
}