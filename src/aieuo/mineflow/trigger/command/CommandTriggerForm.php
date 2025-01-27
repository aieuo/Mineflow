<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\command;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\BaseTriggerForm;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerForm;
use aieuo\mineflow\ui\CommandForm;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class CommandTriggerForm extends TriggerForm {

    public function buildAddedTriggerMenu(ListForm $form, Player $player, Recipe $recipe, Trigger $trigger): void {
        if (!($trigger instanceof CommandTrigger)) return;

        $form->addButton(new Button("@trigger.command.edit.title", function () use($player, $trigger) {
            $manager = Mineflow::getCommandManager();
            $command = $manager->getCommand($manager->getCommandLabel($trigger->getCommand()));
            if ($command === null) {
                $player->sendMessage(Language::get("trigger.command.select.notFound"));
                return;
            }

            (new CommandForm)->sendCommandMenu($player, $command);
        }));
    }

    public function sendMenu(Player $player, Recipe $recipe): void {
        $this->sendSelectCommand($player, $recipe);
    }

    public function sendSelectCommand(Player $player, Recipe $recipe, array $default = [], array $errors = []): void {
        (new CustomForm(Language::get("trigger.command.select.title", [$recipe->getName()])))
            ->setContents([
                new Input("@trigger.command.select.input", "@trigger.command.select.placeholder", $default[0] ?? "", true),
                new CancelToggle(fn() => (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe)),
            ])->onReceive(function (Player $player, array $data, Recipe $recipe) {
                $manager = Mineflow::getCommandManager();
                $original = $manager->getCommandLabel($data[0]);
                if (!$manager->existsCommand($original)) {
                    $this->sendConfirmCreate($player, $original, function (bool $result) use ($player, $recipe, $data) {
                        if ($result) {
                            (new CommandForm)->sendAddCommand($player, [$data[0]]);
                        } else {
                            $this->sendSelectCommand($player, $recipe, $data, [["@trigger.command.select.notFound", 0]]);
                        }
                    });
                    return;
                }

                $trigger = new CommandTrigger($data[0]);
                (new BaseTriggerForm)->tryAddTriggerToRecipe($player, $recipe, $trigger);
            })->addArgs($recipe)->addErrors($errors)->show($player);
    }

    public function sendConfirmCreate(Player $player, string $name, callable $callback): void {
        (new ModalForm("@trigger.command.confirmCreate.title"))
            ->setContent(Language::get("trigger.command.confirmCreate.content", [$name]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(fn (Player $player, ?bool $data) => $callback($data))
            ->addArgs($callback)
            ->show($player);
    }
}