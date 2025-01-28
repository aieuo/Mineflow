<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class MineflowForm {

    public function confirmRename(Player $player, string $name, string $newName, callable $onAccept, callable $onRefuse): void {
        (new ModalForm("@form.home.rename.title"))
            ->setContent(Language::get("form.home.rename.content", [$name, $newName]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, string $name, string $newName, callable $onTrue, callable $onFalse) {
                if ($data) {
                    $onTrue($newName);
                } else {
                    $onFalse($name);
                }
            })->addArgs($name, $newName, $onAccept, $onRefuse)->show($player);
    }

    public function selectRecipe(Player $player, string $title, callable $callback, ?callable $onCancel = null, array $default = []): void {
        ($it = new CustomForm($title))->setContents([
                new Input("@form.recipe.recipeName", "", $default[0] ?? "", true),
                new Input("@form.recipe.groupName", "", $default[1] ?? ""),
                new CancelToggle(fn() => is_callable($onCancel) ? $onCancel() : (new HomeForm)->sendMenu($player)),
            ])->onReceive(function (Player $player, array $data, callable $callback) use($it) {
                $manager = Mineflow::getRecipeManager();

                [$name, $group] = $data;
                if ($group === "") [$name, $group] = $manager->parseName($data[0]);
                if (!$manager->exists($name, $group)) {
                    $it->resend([["@form.recipe.select.notfound", 0]]);
                    return;
                }

                $recipe = $manager->get($name, $group);
                if ($recipe === null) {
                    $it->resend([["@form.recipe.select.notfound", 0]]);
                    return;
                }

                $callback($recipe);
            })->addArgs($callback)->show($player);
    }
}