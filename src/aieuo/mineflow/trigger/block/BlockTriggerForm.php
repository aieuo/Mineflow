<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\block;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\BaseTriggerForm;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerForm;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use function explode;

class BlockTriggerForm extends TriggerForm {

    public function buildAddedTriggerMenu(ListForm $form, Player $player, Recipe $recipe, Trigger $trigger): void {
        if (!($trigger instanceof BlockTrigger)) return;

        $form->addButton(new Button("@trigger.block.warp", function () use($player, $recipe, $trigger) {
            $pos = explode(",", $trigger->getPositionString());
            $level = Server::getInstance()->getWorldManager()->getWorldByName($pos[3]);
            if ($level === null) {
                $this->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.block.world.notfound"]);
                return;
            }

            $player->teleport(new Position((int)$pos[0], (int)$pos[1], (int)$pos[2], $level));
        }));
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        (new ListForm(Language::get("form.trigger.triggerMenu.title", [$recipe->getName(), Triggers::BLOCK])))
            ->addButtons([
                new Button("@form.back", fn() => (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe)),
                new Button("@form.add", function () use($player, $recipe) {
                    Session::getSession($player)->set("blockTriggerAction", "add")->set("blockTriggerRecipe", $recipe);
                    $player->sendMessage(Language::get("trigger.block.add.touch"));
                }),
            ])->show($player);
    }
}